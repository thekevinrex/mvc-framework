<?php


namespace PhpVueBridge\View;

use PhpVueBridge\Bedrock\Application;
use PhpVueBridge\View\Contracts\ViewContract;
use PhpVueBridge\View\Engines\Engine;
use PhpVueBridge\View\Engines\EngineResolver;
use PhpVueBridge\View\Interfaces\ViewFinderInterface;
use PhpVueBridge\View\Managers\Concerns\ManageSections;

/**
 * This is a class that given an view path it returns a view class instance
 */
class ViewFactory implements ViewContract
{
    use ManageSections;

    protected array $shared = [];

    protected array $renderedOnce = [];

    protected array $rendering = [];

    protected array $extensions = [
        'vue.phtml' => 'compiler',
        'phtml' => 'compiler',
        'md' => 'md',
        'html' => 'file',
        'css' => 'file'
    ];

    public function __construct(
        protected Application $app,
        protected EngineResolver $resolver,
        protected ViewFinderInterface $finder
    ) {

        $this->finder->syncExtensions($this->extensions);

        $this->share('_factory', $this);
    }

    public function make($path, $data = [])
    {

        $path = $this->finder->find(
            $view = $this->normalizePath($path)
        );

        $data = $this->parseData($data);

        return $this->viewInstance($view, $path, $data);
    }

    public function share(array|string $key, $value = null): self
    {

        $data = [];

        if (is_array($key)) {
            $data = $key;
        } else {
            $data = [$key => $value];
        }

        $this->shared = array_merge($this->shared, $data);

        return $this;
    }

    protected function normalizePath(string $path): string
    {
        return str_replace('/', '.', $path);
    }

    protected function parseData(array $data): array
    {
        // return $data instanceof \Arrayable ? $data->toArray() : $data;
        return $data;
    }

    protected function viewInstance(string $view, string $path, array $data = [])
    {
        return new View($this, $this->getEngineFromPath($path), $path, $view, $data);
    }

    protected function getEngineFromPath(string $path): Engine
    {
        if (!$extension = $this->finder->getExtension($path)) {
            throw new \InvalidArgumentException('Invalid extension found for path ' . $path);
        }

        $engine = $this->extensions[$extension];

        return $this->resolver->resolve($engine);
    }

    public function addExtension(string $extension, string $engine)
    {
        $this->extensions[$extension] = $engine;

        $this->finder->syncExtensions($this->extensions);
    }

    public function removeExtension(string $extension)
    {
        unset($this->extensions[$extension]);
    }

    public function getShared(): array
    {
        return $this->shared;
    }

    public function startRendering(string $view): void
    {
        $this->rendering[] = $view;
    }

    public function endRendering(): string
    {
        return array_pop($this->rendering);
    }
}
