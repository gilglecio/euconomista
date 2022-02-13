<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Coluna para controlar as categorias que aparecerão no gráfico
 */
class Version20161230232016 extends AbstractMigration
{
    public function up(Schema $schema)
    {
        $table = $schema->getTable('categories');
        $table->addColumn('hexcolor', 'string', [
            'length' => 6,
            'notnull' => false
        ]);
    }

    public function down(Schema $schema)
    {
        $table = $schema->getTable('categories');
        $table->dropColumn('hexcolor');
    }
}
