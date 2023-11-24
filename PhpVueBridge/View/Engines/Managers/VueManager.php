<?php


namespace app\core\view\Engines\Managers;


use app\core\bridge\BridgeHandler;
use app\core\facades\Bridge;
use app\core\view\ComponentAttributeBag;
use app\core\view\ComponentHandler;
use app\core\view\ComponentSlot;

trait VueManager
{

    protected array $scriptQueue = [];
    protected array $vueScripts = [];
    protected array $refs = [];

    public function getVueScripts()
    {
        return $this->vueScripts;
    }

    public function startScript($type)
    {
        if (ob_start()) {
            $this->scriptQueue[] = $type;
        }
    }

    public function endScript()
    {
        $script = array_pop($this->scriptQueue);

        $this->vueScripts[] = [
            $script,
            ob_get_clean(),
        ];
    }

    public function defineRef($name, $value)
    {
        $this->refs[$name] = $value;
    }

    public function getRefs()
    {
        return $this->refs;
    }

    protected array $methods = array();
    protected array $computeds = array();

    public function method($name = null, $param = null)
    {
        if (ob_start())
            $this->methods[] = [$name, $param];
    }

    public function endMethod()
    {
        [$name, $param] = array_pop($this->methods);

        $this->vueScripts['method'][] = [
            $name,
            $param,
            ob_get_clean()
        ];
    }

    public function computed($name = null, $param = null)
    {
        if (ob_start())
            $this->computeds[] = [$name, $param];
    }

    public function endComputed()
    {
        [$name, $param] = array_pop($this->computeds);

        $this->vueScripts['computed'][] = [
            $name,
            $param,
            ob_get_clean()
        ];
    }
}