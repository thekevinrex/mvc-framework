<?php 

    namespace app\Core\DataBase\Connectors;

    class MysqlConector extends Conector
    {
        protected \PDO $connection;
        
        public function connect(array $config)
        {
            
            $dsn = $this->getDSN($config);

            $this->connection = $connection = $this->createPdo($dsn, $config);

            if (isset($config['charset'])){
                $this->setEncoding($config['charset'], $config['collation'] ?? null);
            }

            if (isset($config['timezone'])) {
                $this->configureTimezone($config['timezone']);
            }

            return $connection;
        }

        protected function getDSN(array $config) : string
        {
            $host = $config['host'];
            $port = $config['port'] ?? null;
            $database = $config['database'];

            return is_null($port)
                            ? "mysql:host={$host};port={$port};dbname={$database};"
                            : "mysql:host={$host};dbname={$database};";
        }

        protected function setEncoding(string $charset, string $collation = null): void
        {
            $query = 
                "set names '{$charset}'" 
                . (!is_null($collation)
                        ? " collate '{$collation}'"
                        : '');

            $this->connection->prepare($query)->execute();
        }

        protected function configureTimezone(string $timezone)
        {
            $this->connection->prepare('set time_zone="'.$timezone.'"')->execute();
        }
    }
?>