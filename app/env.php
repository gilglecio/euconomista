<?php

$env = function ($key, $default) {
    $value = getenv($key);

    return $value === false ? $default : $value;
};

return [
    "db" => [
        "dbname" => $env('DB_NAME', 'euconomista'),
        "host" => $env('DB_HOST', 'euconomista_db'),
        "username" => $env('DB_USER', 'euconomista'),
        "password" => $env('DB_PASSWORD', 'euconomista'),
        "driver" => "mysql"
    ],
    "mailer" => [
        "host" => $env('MAIL_HOST', 'euconomista.com.br'),
        "port" => $env('MAIL_PORT', '25'),
        "username" => $env('MAIL_USER', 'euconomista'),
        "password" => $env('MAIL_PASSWORD', ''),
        "secure" => $env('MAIL_SECURE', 'ssl')
    ]
];
