<?php
class Database
{
    private PDO $connection;

    public function __construct(array $config)
    {
        $dsn = sprintf(
            'pgsql:host=%s;dbname=%s;sslmode=require;options=endpoint=%s',
            $config['host'],
            $config['dbname'],
            $config['endpoint']
        );

        $this->connection = new PDO($dsn, $config['user'], $config['password'], [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
        ]);
    }

    public function getConnection(): PDO
    {
        return $this->connection;
    }
}
