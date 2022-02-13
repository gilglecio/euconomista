<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170209215134 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $table = $schema->getTable('releases');
        
        $table->addColumn('child_id', 'integer', [
            'unsigned' => true,
            'notnull' => false
        ]);
        
        $table->addIndex(['child_id'], 'fk_child_idx');

        $table->addForeignKeyConstraint($table, ['child_id'], ['id'], [
            'onDelete' => 'NO ACTION',
            'onUpdate' => 'NO ACTION'
        ], 'fk_releases_child_id');
    }

    public function down(Schema $schema)
    {
        $table = $schema->getTable('releases');

        $table->dropForeignKey('fk_releases_child_id');
        $table->dropColumn('child_id');
    }
}
