<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20161209213638 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $schema->getTable('releases')
            ->addColumn('description', 'string', [
                'length' => 255,
                'notnull' => false
            ]);
    }

    public function down(Schema $schema)
    {
        $schema->getTable('releases')
            ->dropColumn('description');
    }
}
