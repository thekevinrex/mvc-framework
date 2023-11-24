<?php

namespace app\core\bridge\Utils;

class AssetLoader
{

    public function __construct(
        protected Vite $server
    ) {

    }

    public function loadAssets($inputs = [])
    {
        $scripts = [
            $this->loadJsFile('@vite/client')
        ];

        foreach ($inputs as $input) {
            $scripts[] = $this->loadFile($input);
        }

        return implode(PHP_EOL, $scripts);
    }

    protected function loadFile($file)
    {
        $file = trim($file);

        if (str_ends_with($file, '.css')) {
            return $this->loadCssFile($file);
        }

        if (str_ends_with($file, '.js')) {
            return $this->loadJsFile($file);
        }

        return $this->server->formatFile($file);
    }

    protected function loadCssFile($file)
    {
        $prefixUrl = $this->server->formatFile($file);

        //TODO fix css load file
        return '';
    }

    protected function loadJsFile($file, $options = [])
    {

        $options['src'] = $this->server->formatFile($options['src'] ?? $file);

        if (!isset($options['type'])) {
            $options['type'] = 'module';
        }

        return '<script ' . $this->optionsToString($options) . ' ></script>';
    }

    protected function optionsToString($options)
    {
        $string = '';

        foreach ($options as $key => $option) {
            $string .= "{$key}=\"{$option}\" ";
        }

        return $string;
    }

}
?>