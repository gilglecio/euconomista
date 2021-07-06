<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Apagando a coluna `backup_json` da tabela `users`
 */
class Version20161216211433 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $table = $schema->getTable('user_logs');
        $table->dropColumn('backup_json');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $table = $schema->getTable('user_logs');
        $table->addColumn('backup_json', 'text', [
            'notnull' => false
        ]);
    }
}
