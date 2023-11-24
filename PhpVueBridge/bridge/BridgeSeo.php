<?php

namespace app\core\bridge;

use app\core\bridge\Utils\Meta;

class BridgeSeo
{

    protected string $title;

    protected array $metas = [];

    protected string $url;

    protected array $config;

    public function __construct(array $config)
    {
        $this->config = $config;

        $this->autoFill();
    }

    protected function autoFill()
    {
        foreach (['title', 'description', 'keywords'] as $default) {
            if (isset($this->config['default'][$default])) {
                $this->{$default}($this->config['default'][$default]);
            }
        }

        if ($this->config['auto_canonical_link']) {
            // todo
            $this->canonical('canonical_url');
        }

        return $this;
    }

    public function canonical(string $url)
    {
        $this->url = $url;

        return $this;
    }

    public function title(string $title, bool $withPrefixAndSufix = true)
    {
        $titleParts = [];
        $separator = config('seo.title_separator', '');

        if ($withPrefixAndSufix && !empty($prefix = config('seo.title_prefix', ''))) {
            $titleParts[] = trim($prefix);
            $titleParts[] = $separator;
        }

        $titleParts[] = $title;

        if ($withPrefixAndSufix && !empty($sufix = config('seo.title_sufix', ''))) {
            $titleParts[] = $separator;
            $titleParts[] = trim($sufix);
        }

        $this->title = implode(' ', $titleParts);

        return $this;
    }

    public function description(string $des)
    {
        $this->addMeta('description', $des);

        return $this;
    }

    public function addKeyword(string $keyword)
    {
        $meta = $this->getMeta('keywords');

        $keywords = explode(
            ',',
            $meta->getContent()
        );

        $keywords[] = $keyword;

        $meta->setContent(
            implode(',', $keywords)
        );

        return $this;
    }

    public function keywords($keywords)
    {
        $keywords = is_array($keywords)
            ? implode(',', $keywords)
            : $keywords;

        $this->addMeta('keywords', $keywords);

        return $this;
    }

    public function getMeta(string $key)
    {
        return $this->metas[$key] ?? null;
    }

    public function addMeta(string $key, string $content)
    {
        if (isset($this->metas[$key])) {
            $this->metas[$key]->setContent();
        }

        return $this->metas[$key] = new Meta($key, $content);
    }

    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'metas' => $this->metas,
            'canonical' => $this->url,
        ];
    }

    public function renderHead(): string
    {
        return $this->renderTitle() . PHP_EOL . $this->renderMeta();
    }

    public function renderMeta(): string
    {
        $metas = [];

        foreach ($metas as $meta) {
            $metas[] = $meta->render();
        }

        $metas[] = "<link rel=\"canonical\" href=\"{$this->url}\">";

        return implode(PHP_EOL, $metas);
    }

    public function renderTitle()
    {
        return "<title>{$this->title}</title>";
    }
}
?>