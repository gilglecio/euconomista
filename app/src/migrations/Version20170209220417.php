<?php

namespace DoctrineMigrations;

use Doctrine\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20170209220417 extends AbstractMigration
{
    public function up(Schema $schema): void
    {
        $db = $this->connection;
        $rows = $db->fetchAllAssociative('SELECT id, parent_id, child_id FROM releases where parent_id is not null');

        foreach ($rows as $row) {
            $db->executeStatement('update releases set child_id = parent_id where id = ' . $row['id']);           
            $db->executeStatement('update releases set parent_id = null where id = ' . $row['id']);           
        }
    }
    
    public function down(Schema $schema): void
    {
        $db = $this->connection;
        $rows = $db->fetchAllAssociative('SELECT id, parent_id, child_id FROM releases where child_id is not null');

        foreach ($rows as $row) {
            $db->executeStatement('update releases set parent_id = child_id where id = ' . $row['id']);           
            $db->executeStatement('update releases set child_id = null where id = ' . $row['id']);           
        }
    }
}
