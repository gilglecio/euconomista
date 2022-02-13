<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20161203233401 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $table = $schema->createTable('peoples');

        $table->addColumn('id', 'integer', [
            'unsigned' => true,
            'autoincrement' => true,
            'notnull' => true
        ]);

        $table->addColumn('user_id', 'integer', [
            'unsigned' => true,
            'notnull' => true
        ]);

        $table->addColumn('entity', 'integer', [
            'unsigned' => true,
            'notnull' => true
        ]);

        $table->addColumn('name', 'string', [
            'length' => 60
        ]);

        $table->addColumn('created_at', 'datetime', [
            'notnull' => true
        ]);

        $table->addColumn('updated_at', 'datetime', [
            'notnull' => true
        ]);

        $table->setPrimaryKey(['id']);
        
        $table->addIndex(['user_id'], 'fk_user_idx');
        $table->addUniqueIndex(['entity', 'name']);

        $table->addForeignKeyConstraint($schema->getTable('users'), ['user_id'], ['id'], [
            'onDelete' => 'NO ACTION',
            'onUpdate' => 'NO ACTION'
        ], 'fk_peoples_user_id');
    }

    public function down(Schema $schema)
    {
        $schema->dropTable('peoples');
    }
}
