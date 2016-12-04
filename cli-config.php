<?php

require_once 'vendor/autoload.php';

use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Tools\Console\ConsoleRunner;

/**
 * Database params
 */
$db = require 'app/env.php';
$db = $db['db'];

/**
 * @var Setup
 */
$config = Setup::createAnnotationMetadataConfiguration([__DIR__ . '/app/src/'], $isDevMode = true);

/**
 * @var EntityManager
 */
$entityManager = EntityManager::create([
    'dbname' => $db->dbname,
    'user' => $db->username,
    'password' => $db->password,
    'host' => $db->host,
    'driver' => 'mysqli',
], $config);

$platform = $entityManager->getConnection()->getDatabasePlatform();
$platform->registerDoctrineTypeMapping('enum', 'string');

return ConsoleRunner::createHelperSet($entityManager);
