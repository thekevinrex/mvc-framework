<?php

namespace PhpVueBridge\Bridge\Utils;

class Meta implements \JsonSerializable
{
    protected array $attributes = [];

    public function __construct(
        protected string $key,
        protected string $content
    ) {
        $this->attributes['name'] = $key;
        $this->attributes['content'] = $content;
    }

    public function render(): string
    {

        $attributes = [];

        foreach ($this->attributes as $key => $attribute) {
            $attributes[] = "{$key}=\"{$attribute}\"";
        }

        $attributes = implode(' ', $attributes);

        return "<meta {$attributes} />";
    }

    public function getContent()
    {
        return $this->content;
    }

    public function setContent(string $content)
    {
        $this->content = $content;
    }

    public function jsonSerialize(): mixed
    {
        return [
            'attributes' => $this->attributes,
            'key' => $this->key,
            'content' => $this->content,
            'render' => $this->render()
        ];
    }
}
?>