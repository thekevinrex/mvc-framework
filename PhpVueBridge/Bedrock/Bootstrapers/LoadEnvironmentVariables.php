<?php

namespace PhpVueBridge\Bedrock\Bootstrapers;

use PhpVueBridge\Bedrock\Application;
use PhpVueBridge\Support\Env;

class LoadEnvironmentVariables
{

    public function __construct(
        protected Application $app
    ) {
    }

    public function bootstrap($env = null)
    {

        $this->checkActualEnviroment();

        try {

            $dotEnv = \Dotenv\Dotenv::create(
                Env::getRepository(),
                $this->app->getEnviromentPath(),
                $this->app->getEnviromentFile(),
            );

            if (!is_null($dotEnv)) {
                $dotEnv->safeLoad();
            }
        } catch (\Exception $e) {
            throw $e;
        }

    }

    protected function checkActualEnviroment(): void
    {
        if (!is_null($env = Env::get('APP_ENV'))) {
            $this->app->setActualEnviroment($env);
        }
    }

}
?>