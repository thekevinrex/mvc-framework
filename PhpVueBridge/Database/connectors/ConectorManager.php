<?php 

    namespace app\Core\DataBase\Connectors;
    use app\Core\Application;
    use app\Core\DataBase\Connections\MysqlConection;

    class ConectorManager
    {
        
        protected Application $app;

        public function __construct(Application $app) {
            $this->app = $app;  
        }

        public function resolve(array $config)
        {
            return $this->createConnection ($config);
        }

        protected function createConnection(array $config)
        {
            $pdo = $this->createPdoResolver($config);
            
            return match ($config['driver']) {
                 'mysql' => new MysqlConection ($pdo, $config['database'], $config),
                 default => null,
            };
        }

        protected function createPdoResolver(array $config)
        {

            if (!isset($config['driver'])) {
                // #TODO trown exception
                return;
            }

            return function () use ($config) {
                return $this->createConnector($config)->connect($config);
            };
        }

        protected function createConnector(array $config)
        {
            return (match ($config['driver']) {
                 'mysql' => new MysqlConector,
                 default => null,
            });
        }

    }

?>