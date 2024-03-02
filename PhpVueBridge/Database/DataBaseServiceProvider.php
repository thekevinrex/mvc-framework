<?php 

namespace app\Core\DataBase;

use app\Core\DataBase\Connectors\ConectorManager;
use app\Core\Support\ServiceProvider;

class DataBaseServiceProvider extends ServiceProvider {


    public function register(): void
    {
        
        $this->app->bindShared('connections', function ($app) {
            return new ConectorManager ($app);
        });

        $this->app->bindShared('db', function ($app){
            return new DataBaseManager($app, $app->resolve('connections'));
        });
        
    }

}

?>