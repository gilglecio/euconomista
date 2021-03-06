<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20161213213355 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $table = $schema->getTable('release_logs');

        $table->addColumn('parent_id', 'integer', [
            'unsigned' => true,
            'notnull' => false
        ]);

        $table->addIndex(['parent_id'], 'fk_parent_idx');

        $table->addForeignKeyConstraint($table, ['parent_id'], ['id'], [
            'onDelete' => 'NO ACTION', 
            'onUpdate' => 'NO ACTION'
        ], 'fk_release_logs_parent_id');
    }

    public function down(Schema $schema)
    {
        $table = $schema->getTable('release_logs');

        $table->dropForeignKey('fk_release_logs_parent_id');
        $table->dropColumn('parent_id');
    }
}
