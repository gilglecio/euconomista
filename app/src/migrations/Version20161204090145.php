<?php

namespace DoctrineMigrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Create `releases` table
 */
class Version20161204090145 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $table = $schema->createTable('releases');

        $table->addColumn('id', 'integer', [
            'unsigned' => true,
            'autoincrement' => true,
            'notnull' => true
        ]);

        $table->addColumn('user_id', 'integer', [
            'unsigned' => true,
            'notnull' => true
        ]);

        $table->addColumn('category_id', 'integer', [
            'unsigned' => true,
            'notnull' => true
        ]);

        $table->addColumn('people_id', 'integer', [
            'unsigned' => true,
            'notnull' => true
        ]);

        $table->addColumn('entity', 'integer', [
            'unsigned' => true,
            'notnull' => true
        ]);

        $table->addColumn('number', 'string', [
            'length' => 15
        ]);

        $table->addColumn('value', 'decimal', [
            'notnull' => true,
            'scale' => 2,
            'precision' => 10
        ]);

        $table->addColumn('natureza', 'integer', [
            'notnull' => true
        ]);

        $table->addColumn('data_vencimento', 'date', [
            'notnull' => true
        ]);

        $table->addColumn('status', 'integer', [
            'notnull' => true,
            'default' => 1
        ]);

        $table->addColumn('created_at', 'datetime', [
            'notnull' => true
        ]);

        $table->addColumn('updated_at', 'datetime', [
            'notnull' => true
        ]);

        $table->addColumn('process', 'string', [
            'notnull' => true,
            'length' => 60
        ]);
        
        $table->setPrimaryKey(['id']);

        $table->addIndex(['user_id'], 'fk_user_idx');
        $table->addIndex(['category_id'], 'fk_category_idx');
        $table->addIndex(['people_id'], 'fk_people_idx');
        
        $table->addUniqueIndex(['entity', 'number', 'people_id', 'data_vencimento']);

        $table->addForeignKeyConstraint($schema->getTable('users'), ['user_id'], ['id'], [
            'onDelete' => 'NO ACTION', 
            'onUpdate' => 'NO ACTION'
        ], 'fk_releases_user_id');

        $table->addForeignKeyConstraint($schema->getTable('categories'), ['category_id'], ['id'], [
            'onDelete' => 'NO ACTION', 
            'onUpdate' => 'NO ACTION'
        ], 'fk_releases_category_id');

        $table->addForeignKeyConstraint($schema->getTable('peoples'), ['people_id'], ['id'], [
            'onDelete' => 'NO ACTION', 
            'onUpdate' => 'NO ACTION'
        ], 'fk_releases_people_id');

    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        if ($schema->hasTable('releases')) {
            $schema->dropTable('releases');
        }
    }
}
