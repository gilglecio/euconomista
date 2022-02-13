<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20161221234206 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $this->addSql('update users set status = 1');
    }

    public function down(Schema $schema)
    {
        $this->addSql('update users set status = NULL');
    }
}
