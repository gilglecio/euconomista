<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Adicionando uma coluna para o usuário informar uma descrição para o lançamento.
 */
class Version20161209213638 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $schema->getTable('releases')
            ->addColumn('description', 'string', [
                'length' => 255,
                'notnull' => false
            ]);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $schema->getTable('releases')
            ->dropColumn('description');
    }
}
