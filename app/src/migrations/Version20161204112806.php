<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create `release_logs` table
 */
class Version20161204112806 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $table = $schema->createTable('release_logs');

        $table->addColumn('id', 'integer', [
            'unsigned' => true,
            'autoincrement' => true,
            'notnull' => true
        ]);

        $table->addColumn('release_id', 'integer', [
            'unsigned' => true,
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

        $table->addColumn('action', 'integer', [
            'unsigned' => true,
            'notnull' => true
        ]);

        $table->addColumn('value', 'decimal', [
            'notnull' => true,
            'scale' => 2,
            'precision' => 10
        ]);

        $table->addColumn('backup', 'text', [
            'notnull' => false
        ]);

        $table->addColumn('created_at', 'datetime', [
            'notnull' => true
        ]);

        $table->addColumn('date', 'date', [
            'notnull' => true
        ]);

        $table->setPrimaryKey(['id']);

        $table->addIndex(['user_id'], 'fk_user_idx');
        $table->addIndex(['release_id'], 'fk_release_idx');

        $table->addForeignKeyConstraint($schema->getTable('users'), ['user_id'], ['id'], [
            'onDelete' => 'NO ACTION', 
            'onUpdate' => 'NO ACTION'
        ], 'fk_release_logs_user_id');

        $table->addForeignKeyConstraint($schema->getTable('releases'), ['release_id'], ['id'], [
            'onDelete' => 'NO ACTION', 
            'onUpdate' => 'NO ACTION'
        ], 'fk_release_logs_release_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        if ($schema->hasTable('release_logs')) {
            $schema->dropTable('release_logs');
        }
    }
}
