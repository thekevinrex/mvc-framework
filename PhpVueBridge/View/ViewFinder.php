<?php

namespace PhpVueBridge\View;

use Closure;
use PhpVueBridge\Bedrock\Application;
use PhpVueBridge\View\Interfaces\ViewFinderInterface;

class ViewFinder implements ViewFinderInterface
{

    protected array $extensions = [];

    protected array $cacheViews = [];

    public function __construct(
        protected Application $app,
        protected string|array $paths,
    ) {
        $this->paths = array_map($this->resolvePath(), $paths);
    }

    public function find(string $view): string
    {
        if (isset($this->cacheViews[$view])) {
            return $this->cacheViews[$view];
        }

        return $this->cacheViews[$view] = $this->findInPaths($view, $this->paths);
    }

    public function getExtension(string $path): string
    {
        foreach ($this->extensions as $extension) {
            if (str_ends_with($path, '.' . $extension)) {
                return $extension;
            }
        }

        return false;
    }

    protected function findInPaths(string $view, string|array $paths): string
    {

        $paths = is_array($paths) ? $paths : [$paths];

        foreach ($paths as $path) {
            foreach ($this->getPathsForView($view) as $posibleView) {
                if (file_exists($viewPath = $path . '/' . $posibleView)) {
                    return $viewPath;
                }
            }
        }

        throw new \InvalidArgumentException("View [{$view}] not found");
    }

    protected function getPathsForView($view): array
    {
        return array_map(fn ($ext) => str_replace('.', '/', $view) . '.' . $ext, $this->extensions);
    }

    public function syncExtensions(array $extension): void
    {
        $this->extensions = array_keys($extension);
    }

    protected function resolvePath(): Closure
    {
        return fn ($path) => realpath($path) ?: $path;
    }
}
