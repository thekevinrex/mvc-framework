<?php


namespace PhpVueBridge\View\Compilers;

use PhpVueBridge\View\Compilers\Parser\Token;
use PhpVueBridge\View\Compilers\Parser\Parser;
use PhpVueBridge\View\Compilers\Parser\TokenTree;
use PhpVueBridge\View\Compilers\Parser\Nodes\Node;
use PhpVueBridge\View\Compilers\Parser\TreeNavegator;
use PhpVueBridge\View\Compilers\Exceptions\CompilerFileException;
use PhpVueBridge\View\Compilers\Parser\Nodes\TemplateNode;
use PhpVueBridge\View\Compilers\Parser\Nodes\UnCompiledNode;
use PhpVueBridge\View\Compilers\Parser\Tokens\TextToken;

abstract class Compiler
{

    protected array $contentTags = ['{{', '}}'];

    protected string $echoFormat = 'e(%s)';

    protected array $compileMethods = [];

    protected array $directives = [];

    protected array $footer = [];

    protected array $rawBlocks = [];

    protected array $filters = [];

    public function __construct(
        protected CompilerFactory $factory,
    ) {

        $this->registerDirectives();
    }

    protected function registerDirectives()
    {
        $this->directives = $this->factory->getDirectives();
    }

    protected function compile(string $content): string
    {
        [$this->footer, $result] = [[], ''];

        $tokenStream = (new Parser($content))->parse();

        $tokenTree = (new TokenTree($tokenStream))->build();

        $navegator = new TreeNavegator($tokenTree->getTree());

        // Precompilers 
        foreach ($this->factory->getPrecompilers() as $precompiler) {
            $content = $precompiler($navegator);
        }

        // echo $tokenTree;


        $tokenTree->preCompile(
            $navegator,
            function (TemplateNode $node) {
                $directiveNode = $node->getNode();
                $token = $directiveNode->getToken();
                $directive = $token->getDirective();

                if (str_starts_with($directive, '@')) {
                    return $node->textNode(
                        substr($token->getContent(), 1),
                    );
                }

                if (in_array($directive, array_keys($this->directives))) {
                    return call_user_func($this->directives[$directive], $node);
                } else if (method_exists($this, 'compile' . ucfirst($directive))) {

                    return (count($token->getArguments()) > 0)
                        ? $this->{'compile' . ucfirst($directive)}($node, $token->getArguments())
                        : $this->{'compile' . ucfirst($directive)}($node);
                }

                return $node->textNode(
                    $token->getContent()
                );
            }
        );

        echo $tokenTree;

        // Compile tags
        $this->compileBridgeTags(
            $navegator
        );

        // Compile Components
        $this->compileComponentsTags(
            $navegator
        );


        // Compile Directives

        // exit;

        return $tokenTree->compile();

        foreach (token_get_all($content) as $token) {
            $result .= is_array($token) ? $this->parseToken($token) : $token;
        }

        $result = $result
            . PHP_EOL
            . implode(PHP_EOL, $this->footer);

        if (count($this->rawBlocks) > 0) {
            $result = $this->restoreRawBlocks($result);
        }

        return $result;
    }

    public function make(
        string $path
    ) {

        $content = $this->compile(
            $this->getContent($path)
        );

        $content .= $this->appendFilePath($path);

        $this->save($path, $content);
    }

    protected function getContent(string $path): string
    {
        try {
            return file_get_contents($path);
        } catch (\Throwable $e) {
            throw new CompilerFileException('Could not read file ' . $path, $e->getCode(), $e);
        }
    }

    protected function save(string $path, string $content)
    {
        if (!file_put_contents($this->factory->getCompiledPath($path), $content)) {
            throw new CompilerFileException('Could not save compiled file ' . $path);
        }
    }

    protected function parseToken(array $token)
    {

        [$id, $content] = $token;

        if ($id == T_INLINE_HTML) {

            foreach ($this->compileMethods as $method) {
                $content = $this->{'compile' . ucfirst($method)}($content);
            }

            $content = $this->compileDirectives($content);
        }

        return $content;
    }

    protected function storeRawBlocks(string $content)
    {
        if (str_contains($content, '@php')) {
            $content = $this->storePhpRawBlock($content);
        }

        if (str_contains($content, '@js')) {
            $content = $this->storeJSRawBlock($content);
        }

        return $content;
    }

    protected function storePHPRawBlock(string $content)
    {
        return preg_replace_callback('/(?<!@)@php(.*?)@endphp/s', function ($matches) {
            return $this->storeRawBlock("<?php{$matches[1]}?>");
        }, $content);
    }

    protected function storeJSRawBlock(string $content)
    {
        return preg_replace_callback('/(?<!@)@js(.*?)@endjs/s', function ($matches) {
            return $this->storeRawBlock($matches[1]);
        }, $content);
    }

    protected function storeRawBlock(string $save)
    {

        $raw_id = md5('block' . time());

        $this->rawBlocks[$raw_id] = $save;

        return $this->getRawBlockName($raw_id);
    }

    protected function restoreRawBlocks($content)
    {
        foreach ($this->rawBlocks as $block_id => $block) {
            $content = str_replace(
                $this->getRawBlockName($block_id),
                $block,
                $content
            );
        }

        $this->rawBlocks = [];

        return $content;
    }

    protected function getRawBlockName(string $block_id)
    {
        return sprintf('__raw_block_%s__', $block_id);
    }

    protected function compileComponentsTags(TreeNavegator $navegator)
    {
        (
            new ComponentTagCompiler($navegator)
        )->compile();
    }

    protected function compileBridgeTags(TreeNavegator $navegator): void
    {
        // return (new BridgeTagCompiler)->compile($content);
    }

    protected function compileDirectives($content)
    {
        // return preg_replace_callback('/@(\w+)\s*\(\s*(.*?)\s*\)/is', function ($matches) {
        return preg_replace_callback('/\B@(@?\w+(?:::\w+)?)(\s*\( ( [\S\s]*? ) \))?/x', function ($matches) {

            if (str_starts_with($matches[1], '@')) {
                return $matches[1];
            }

            $directive = [
                0 => $matches[0],
                1 => $matches[1],
                2 => $matches[3] ?? null,
            ];

            if (in_array($matches[1], array_keys($this->directives))) {
                return call_user_func($this->directives[$matches[1]], $directive);
            } else if (method_exists($this, $matches[1])) {

                return (!is_null($directive[2]))
                    ? $this->{$matches[1]}($directive)
                    : $this->{$matches[1]}();
            }

            return $matches[0];
        }, $content);
    }

    protected function appendFilePath($path): string
    {
        return PHP_EOL . "<?php /**PATH {$path} ENDPATH**/ ?>";
    }
}
