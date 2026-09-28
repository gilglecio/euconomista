<?php

use Doctrine\DBAL\DriverManager;

$db = (require __DIR__ . '/app/env.php')['db'];

$connection = DriverManager::getConnection([
    'dbname' => $db['dbname'],
    'user' => $db['username'],
    'password' => $db['password'],
    'host' => $db['host'],
    'driver' => 'pdo_mysql',
    'charset' => 'utf8',
]);

$connection->getDatabasePlatform()->registerDoctrineTypeMapping('enum', 'string');

return $connection;
