<?php


namespace app\core\view\Engines\Managers;


trait SectionsManager
{

    protected array $sections = [];

    protected array $loadingSection = [];

    public function yieldContent($key)
    {
        return ($this->sections[$key]) ?? '';
    }

    public function startSection($section)
    {
        if (ob_start())
            $this->loadingSection[] = $section;
    }

    public function endSection()
    {
        $last = array_pop($this->loadingSection);

        $this->registerSection($last, ob_get_clean());

        return $last;
    }

    public function registerSection($key, $callback)
    {
        $this->sections[$key] = $callback;
    }

}