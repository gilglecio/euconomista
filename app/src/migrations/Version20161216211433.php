<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20161216211433 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $table = $schema->getTable('user_logs');
        $table->dropColumn('backup_json');
    }

    public function down(Schema $schema)
    {
        $table = $schema->getTable('user_logs');
        $table->addColumn('backup_json', 'text', [
            'notnull' => false
        ]);
    }
}
