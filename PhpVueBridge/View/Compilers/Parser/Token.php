<?php

namespace PhpVueBridge\View\Compilers\Parser;

use PhpVueBridge\Support\Env;

abstract class Token
{

    /**
     * All the types of tokens
     */
    const TEXT_TOKEN = 'text';

    const DIRECTIVE_TOKEN = 'directive';

    const OPEN_TAG_TOKEN = 'open_tag';

    const OPEN_CUSTOM_TAG_TOKEN = 'open_custom_tag';

    const AUTO_CLOSE_CUSTOM_TAG_TOKEN = 'auto_close_custom_tag';

    const CLOSE_TAG_TOKEN = 'close_tag';

    const AUTO_CLOSE_TAG_TOKEN = 'auto_close_tag';

    const OPEN_CONTENT_TOKEN = 'open_content';

    const CLOSE_CONTENT_TOKEN = 'close_content';

    const IN_TAG_DIRECTIVE_TOKEN = 'in_tag_directive';

    const IN_TAG_OPEN_CONTENT_TOKEN = 'in_tag_open_content';

    const EXPRESION_TOKEN = 'expression';

    const EOF_TOKEN = 'end_of_file';

    protected string $content;

    protected string $token;

    public function __construct(
        string $token,
        string $content,
    ) {
        $this->token = $token;
        $this->content = trim($content);
    }

    public function getType(): string
    {
        return $this->token;
    }

    public function getContent(): string
    {
        return $this->content;
    }

    public function __toString()
    {
        return $this->token . "(" . e($this->content) . ")";
    }
}
