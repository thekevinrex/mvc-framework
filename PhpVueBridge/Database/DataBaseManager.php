<?php 

    namespace app\Core\DataBase;
    use app\Core\Application;
    use app\Core\DataBase\Connectors\ConectorManager;

    class DataBaseManager
    {
        
        protected Application $app;

        protected ConectorManager $connections;

        protected array $resolvedConnections;

        protected $connection = null;

        public function __construct(Application $app, ConectorManager $manager) {
            $this->app = $app;
            $this->connections = $manager;
        }

        public function make(string $driver = null)
        {
            $driver = $driver ?: config('database.default');
            $connections = config('database.connections', []);

            if (empty($connections) || !in_array($driver, array_keys($connections))) {
                return;
            }

            if (isset($this->resolvedConnections[$driver]))
            {
                return $this->resolvedConnections[$driver];
            }

            $connection = $this->connections->resolve($connections[$driver]);
            
            $this->resolvedConnections[$driver] = $connection;

            return $connection;
        }

        public function connection(string $driver = null)
        {
            $driver = $driver ?: config('database.default');

            if (is_null($this->connection) || empty($this->resolvedConnections)) {
                return $this->connection = $this->make($driver);
            }
            
            return $this->connection;
        }


    }
?>