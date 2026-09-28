<?php

return [
    'table_storage' => [
        'table_name' => 'doctrine_migration_versions',
    ],
    'migrations_paths' => [
        'DoctrineMigrations' => __DIR__ . '/app/src/migrations',
    ],
    // DDL no MySQL faz commit implícito, então não envolvemos as migrations em transação
    'transactional' => false,
];
