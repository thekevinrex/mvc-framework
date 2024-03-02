<?php 

    namespace app\Core\DataBase\Connectors;

    abstract class Conector
    {
        
        protected function createPdo(string $dsn, array $config)
        {
            $username = $config['username'] ?? null;
            $password = $config['password'] ?? null;

            try {
                return new \PDO($dsn, $username, $password);
            } catch (\Throwable $e) {
                throw $e;
            }
        }
    }
?>