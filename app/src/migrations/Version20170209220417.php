<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20170209220417 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $db = $this->connection;
        $rows = $db->fetchAll('SELECT id, parent_id, child_id FROM releases where parent_id is not null');

        foreach ($rows as $row) {
            $db->query('update releases set child_id = parent_id where id = ' . $row['id']);           
            $db->query('update releases set parent_id = null where id = ' . $row['id']);           
        }
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $db = $this->connection;
        $rows = $db->fetchAll('SELECT id, parent_id, child_id FROM releases where child_id is not null');

        foreach ($rows as $row) {
            $db->query('update releases set parent_id = child_id where id = ' . $row['id']);           
            $db->query('update releases set child_id = null where id = ' . $row['id']);           
        }
    }
}
