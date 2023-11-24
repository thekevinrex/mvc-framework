<?php


namespace app\core\view\Engines;


use app\Core\Application;
use app\core\contracts\ViewContract;
use app\core\view\Compilers\CompilerFactory;
use app\core\view\Component;

class CompilerEngine extends PhPEngine
{

    protected CompilerFactory $compiler;

    protected Application $app;

    protected array $compiledFiles = [];

    protected ViewEngine $viewEngine;

    public function __construct(CompilerFactory $compiler, Application $app)
    {
        $this->compiler = $compiler;
        $this->app = $app;
    }


    public function get(string $path, array $data = [], $isComponent = false)
    {
        if (!isset($data['_engine'])) {
            $data['_engine'] = $this->getEngine($path, $isComponent);
        }

        return parent::get(
            $this->compiler->getCompiledPath($path),
            $data
        );
    }

    protected function verifyIsCompiled($path, $data = [], $isComponent = false)
    {

        if (!isset($this->compiledFiles[$path]) && $this->compiler->shouldCompile($path)) {
            $this->compiler->compile($path, $data, $isComponent);
        }

        return $this->compiledFiles[$path] = true;
    }

    public function render($path, array $data = [])
    {
        if ($path instanceof Component) {
            return $this->renderComponent($path, $data);
        }

        if ($path instanceof ViewContract) {
            return $this->renderView($path, $data);
        }

        if (!$this->verifyIsCompiled($path, $data)) {
            return false;
        }

        return $this->get($path, $data);
    }

    public function renderComponent($component, array $data = [])
    {
        return $this->renderView($component->render(), $data, true);
    }

    public function renderView($view, array $data = [], $isComponent = false)
    {

        if (!$this->verifyIsCompiled($view->getView(), $data, $isComponent)) {
            return false;
        }

        $engine = $this->getEngine($view->getView(), $isComponent);

        return [
            $this->get($view->getView(), array_merge($data, ['_engine' => $engine]), $isComponent),
            $engine,
        ];
    }

    public function getEngine($path, $isComponent = false)
    {

        $path = $this->compiler->viewPath($path);

        if ($isComponent && str_ends_with($path, '.vue.phtml')) {
            return $this->getDefaultVueEngine();
        }

        if (ob_get_level() == 1) {
            return $this->viewEngine = $this->getDefaultEngine();
        }

        return $this->viewEngine;
    }

    public function getDefaultVueEngine(): VueEngine
    {
        return new VueEngine($this);
    }

    public function getDefaultEngine(): ViewEngine
    {
        return new ViewEngine($this);
    }
}