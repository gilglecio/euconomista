<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

class Version20161216214822 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $table = $schema->getTable('users');

        $table->addColumn('status', 'integer', [
            'unsigned' => true,
            'notnull' => true,
            'default' => 0
        ]);

        $table->addColumn('password_token', 'string', [
            'notnull' => false,
            'length' => 60
        ]);

        $table->addColumn('password_token_date', 'datetime', [
            'notnull' => false,
        ]);

        $table->addColumn('confirm_email_token', 'string', [
            'notnull' => false,
            'length' => 60
        ]);

        $table->addColumn('confirm_email_token_date', 'datetime', [
            'notnull' => false,
        ]);
    }

    public function down(Schema $schema)
    {
        $table = $schema->getTable('users');

        $table->dropColumn('status');
        $table->dropColumn('password_token');
        $table->dropColumn('password_token_date');
        $table->dropColumn('confirm_email_token');
        $table->dropColumn('confirm_email_token_date');
    }
}
