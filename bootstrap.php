<?php

if (PHP_SAPI == 'cli-server') {
    
    // To help the built-in PHP dev server, check if the request was actually for
    // something which should probably be served as a static file
    $file = __DIR__ . $_SERVER['REQUEST_URI'];

    if (is_file($file)) {
        return false;
    }
}

define('APP_URL', 'http://' . $_SERVER['HTTP_HOST']);

session_start();

$composer = __DIR__ . '/vendor/autoload.php';

if (! file_exists($composer)) {
    die('Run composer install');
}

require $composer;

use Slim\App;
use ActiveRecord\Config;

$settings = require __DIR__ . '/app/settings.php';
$env = require __DIR__ . '/app/env.php';

$settings['settings'] = array_merge($settings['settings'], (array) $env);

Config::initialize(function ($cfg) use ($settings) {
    
    $db = $settings['settings']['db'];
    
    $cfg->set_model_directory($settings['settings']['models_path']);
    $cfg->set_connections([
        // 'development' => 'mysql://username:password@localhost/database_name'
        'development' => sprintf(
            '%s://%s:%s@%s/%s?charset=utf8',
            $db->driver,
            $db->username,
            $db->password,
            $db->host,
            $db->dbname
        )
    ]);
});

$app = new App($settings);

// Set up dependencies
require __DIR__ . '/app/dependencies.php';

// Register middleware
require __DIR__ . '/app/middleware.php';

// Register routes
require __DIR__ . '/app/routes.php';

// Run!
$app->run();