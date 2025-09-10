<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version0001Date20250909 extends SimpleMigrationStep {
    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        if ($schema->hasTable('egonextapp_coda')) {
            return null;
        }

        $table = $schema->createTable('egonextapp_coda');
        $table->addColumn('id', 'integer', ['autoincrement' => true, 'notnull' => true]);
        $table->addColumn('user_id', 'string', ['length' => 64, 'notnull' => true]);
        $table->addColumn('path', 'string', ['length' => 4096, 'notnull' => true]);
        $table->addColumn('size', 'bigint', ['notnull' => true, 'default' => 0]);
        $table->addColumn('mimetype', 'string', ['length' => 255, 'notnull' => true, 'default' => '']);
        $table->addColumn('mtime', 'bigint', ['notnull' => true, 'default' => 0]);
        $table->addColumn('created_at', 'bigint', ['notnull' => true, 'default' => 0]);

        $table->setPrimaryKey(['id']);
        $table->addIndex(['user_id'], 'egonextapp_coda_user_idx');
        $table->addIndex(['created_at'], 'egonextapp_coda_created_idx');

        return $schema;
    }
}
