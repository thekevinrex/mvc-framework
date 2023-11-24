<?php


namespace app\core\view\Compilers;


use app\Core\Application;

abstract class Compiler
{

    protected CompilerFactory $factory;

    protected array $compileMethods = [];

    protected string $path;

    protected array $data = [];

    protected array $directives = [];

    protected array $footer = [];

    /**
     * The class namespaces used for the view classes
     */
    protected string $componentNamespace = '\\app\\App\\View\\Components\\';

    public function __construct(CompilerFactory $factory)
    {
        $this->factory = $factory;

        $this->registerDirectives();
    }

    protected function registerDirectives()
    {
        $this->directives = $this->factory->getDirectives();
    }

    abstract protected function compile(string $content): string;

    public function make(string $path, array $data = array())
    {
        $this->path = $path;
        $this->data = $data;

        $content = $this->get($this->path);

        [$this->footer, $result] = [[], ''];

        foreach (token_get_all($content) as $token) {
            $result .= is_array($token) ? $this->parseToken($token) : $token;
        }

        $content = $this->compile(
            $result
        );

        $content = $content
            . PHP_EOL
            . implode(PHP_EOL, $this->footer);

        $content .= $this->appendFilePath();

        $this->save($content);
    }

    protected function get($file)
    {
        try {
            return file_get_contents($this->factory->viewPath($file));
        } catch (\Throwable $e) {
            throw $e;
        }
    }

    protected function save($content)
    {
        file_put_contents($this->factory->getCompiledPath($this->path), $content);
    }

    protected function parseToken(array $token)
    {

        [$id, $content] = $token;

        if ($id == T_INLINE_HTML) {
            foreach ($this->compileMethods as $method) {
                $content = $this->{'compile' . $method}($content);
            }

            $content = $this->compileDirectives($content);
        }

        return $content;
    }

    protected function compileDirectives($content)
    {
        return preg_replace_callback('/@(\w+)\s*\(\s*(.*?)\s*\)/is', function ($matches) {

            if (in_array($matches[1], array_keys($this->directives))) {
                return call_user_func($this->directives[$matches[1]], $matches);
            } else if (method_exists($this, $matches[1])) {
                return $this->{$matches[1]}($matches);
            }

            return $matches[0];
        }, $content);
    }

    protected function appendFilePath(): string
    {
        return PHP_EOL . "<?php /**PATH {$this->factory->viewPath($this->path)} ENDPATH**/ ?>";
    }
}