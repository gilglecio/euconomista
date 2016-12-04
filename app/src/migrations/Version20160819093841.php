<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create table `users`
 */
class Version20160819093841 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $table = $schema->createTable('users');
        
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
            'notnull' => false
        ]);

        $table->addColumn('name', 'string', [
            'length' => 45,
            'notnull' => true
        ]);

        $table->addColumn('email', 'string', [
            'length' => 60,
            'notnull' => true
        ]);

        $table->addColumn('password', 'string', [
            'length' => 60,
            'notnull' => true
        ]);

        $table->addColumn('created_at', 'datetime', [
            'notnull' => true
        ]);

        $table->addColumn('updated_at', 'datetime', [
            'notnull' => true
        ]);

        $table->setPrimaryKey(['id']);
        
        $table->addIndex(['user_id'], 'fk_user_idx');
        $table->addUniqueIndex(['email']);

        $table->addForeignKeyConstraint($schema->getTable('users'), ['user_id'], ['id'], [
            'onDelete' => 'NO ACTION', 
            'onUpdate' => 'NO ACTION'
        ], 'fk_users_user_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        if ($schema->hasTable('users')) {
            $schema->dropTable('users');
        }
    }
}
