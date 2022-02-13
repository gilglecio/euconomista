<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20161209214305 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $table = $schema->createTable('user_logs');

        $table->addColumn('id', 'integer', [
            'unsigned' => true,
            'autoincrement' => true,
            'notnull' => true
        ]);

        $table->addColumn('entity', 'integer', [
            'unsigned' => true,
            'notnull' => true
        ]);

        $table->addColumn('user_id', 'integer', [
            'unsigned' => true,
            'notnull' => true
        ]);

        $table->addColumn('action', 'string', [
            'length' => 45,
            'notnull' => true
        ]);

        $table->addColumn('class_name', 'string', [
            'length' => 60,
            'notnull' => true
        ]);

        $table->addColumn('row_id', 'integer', [
        	'unsigned' => true,
            'notnull' => true
        ]);

        $table->addColumn('description', 'string', [
            'length' => 255,
            'notnull' => true
        ]);

        $table->addColumn('backup_json', 'text', [
            'notnull' => false
        ]);

        $table->addColumn('created_at', 'datetime', [
            'notnull' => true
        ]);

        $table->addColumn('restored_at', 'datetime', [
            'notnull' => false
        ]);

        $table->setPrimaryKey(['id']);
        
        $table->addIndex(['user_id'], 'fk_user_idx');

        $table->addForeignKeyConstraint($schema->getTable('users'), ['user_id'], ['id'], [
            'onDelete' => 'NO ACTION', 
            'onUpdate' => 'NO ACTION'
        ], 'fk_user_logs_user_id');
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('user_logs');
    }
}
