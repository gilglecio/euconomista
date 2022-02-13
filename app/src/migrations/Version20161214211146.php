<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20161214211146 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $table = $schema->getTable('releases');
        
        $table->addColumn('parent_id', 'integer', [
            'unsigned' => true,
            'notnull' => false
        ]);
        
        $table->addIndex(['parent_id'], 'fk_parent_idx');

        $table->addForeignKeyConstraint($table, ['parent_id'], ['id'], [
            'onDelete' => 'NO ACTION',
            'onUpdate' => 'NO ACTION'
        ], 'fk_releases_parent_id');
    }

    public function down(Schema $schema)
    {
        $table = $schema->getTable('releases');

        $table->dropForeignKey('fk_releases_parent_id');
        $table->dropColumn('parent_id');
    }
}
