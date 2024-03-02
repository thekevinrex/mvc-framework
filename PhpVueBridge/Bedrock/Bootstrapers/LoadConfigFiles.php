<?php

namespace PhpVueBridge\Bedrock\Bootstrapers;

use PhpVueBridge\Bedrock\Application;
use PhpVueBridge\Bedrock\Config;


class LoadConfigFiles
{

    protected Application $app;

    public function __construct(Application $app)
    {
        $this->app = $app;
    }

    public function bootstrap()
    {
        $config = new Config($this->app, $this->loadConfigFromFiles());

        $config->setEncoding('UTF-8');

        $config->setDefaultTimeZone($config->get('app.timezone', 'UTC'));

        $this->app->instance('config', $config);
    }

    /**
     * Load of the config files in the config folder
     * @return array
     */
    protected function loadConfigFromFiles()
    {

        $realPath = realpath(
            $configPath = $this->app->getConfigPath()
        );

        $dir = scandir($realPath);
        $items = [];

        foreach ($dir as $name) {
            if (preg_match('/\.(?:php)$/', $name)) {

                $pos = strpos($name, '.');
                $key = substr($name, 0, $pos);

                $items[$key] = require $configPath . '/' . $name;
            }
        }

        return $items;
    }
}