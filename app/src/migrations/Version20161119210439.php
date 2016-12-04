<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create user `user@mail.com` with password `123456`
 */
class Version20161119210439 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("
            INSERT INTO users SET
            email = 'user@mail.com',
            name = 'User',
            entity = 1,
            created_at = now(),
            updated_at = now(),
            password = '$2y$10$SWo0GUMhbLM6VBklCC01.esZgALq7M2SH7VfMqq72xcTWcjbgRN7i'
        ");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs

    }
}
