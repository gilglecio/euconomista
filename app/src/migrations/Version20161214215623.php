<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Permitindo ue o campo process em releases seja nulo.
 * Este campo só deve ser preenchido em lançamentos divididos.
 */
class Version20161214215623 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $this->addSql("ALTER TABLE  `releases` CHANGE  `process`  `process` VARCHAR( 60 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NULL");
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql("ALTER TABLE  `releases` CHANGE  `process`  `process` VARCHAR( 60 ) CHARACTER SET utf8 COLLATE utf8_unicode_ci NOT NULL");
    }
}
