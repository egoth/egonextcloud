<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;

class Version0002Date20251031 extends SimpleMigrationStep {

    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        // 1) Rinomina vecchia tabella coda -> coda_nuovi_files (se esiste)
        if ($schema->hasTable('egonextapp_coda') && !$schema->hasTable('coda_nuovi_files')) {
            $schema->renameTable('egonextapp_coda', 'coda_nuovi_files');
        }

        // 2) Crea/aggiorna tabella coda_nuovi_files
        if (!$schema->hasTable('coda_nuovi_files')) {
            $t = $schema->createTable('coda_nuovi_files');
            $t->addColumn('id', 'bigint', ['autoincrement' => true, 'notnull' => true]);
            $t->addColumn('user_id', 'string', ['length' => 64, 'notnull' => true]);
            $t->addColumn('path', 'string', ['length' => 4096, 'notnull' => true]);
            $t->addColumn('size', 'bigint', ['notnull' => true, 'default' => 0]);
            $t->addColumn('mimetype', 'string', ['length' => 255, 'notnull' => true, 'default' => '']);
            $t->addColumn('mtime', 'bigint', ['notnull' => true]);
            $t->addColumn('created_at', 'bigint', ['notnull' => true]);
            $t->setPrimaryKey(['id']);
            $t->addIndex(['user_id'], 'coda_usr_idx');
            $t->addIndex(['path'], 'coda_path_idx');
            $t->addIndex(['created_at'], 'coda_created_idx');
        }

        // 3) Crea/aggiorna tabella tasks_attivi
        if ($schema->hasTable('egonextapp_active_tasks') && !$schema->hasTable('tasks_attivi')) {
            $schema->renameTable('egonextapp_active_tasks', 'tasks_attivi');
        }
        if (!$schema->hasTable('tasks_attivi')) {
            $t = $schema->createTable('tasks_attivi');
            $t->addColumn('id', 'bigint', ['autoincrement' => true, 'notnull' => true]);
            $t->addColumn('path', 'string', ['length' => 4096, 'notnull' => true]);
            $t->addColumn('taskname', 'string', ['length' => 64, 'notnull' => true]);
            $t->addColumn('started', 'smallint', ['notnull' => true, 'default' => 0]);
            $t->addColumn('done', 'smallint', ['notnull' => true, 'default' => 0]);
            $t->addColumn('started_at', 'bigint', ['notnull' => true, 'default' => 0]);
            $t->addColumn('done_at', 'bigint', ['notnull' => true, 'default' => 0]);
            $t->setPrimaryKey(['id']);
            $t->addUniqueIndex(['path', 'taskname'], 'tasks_path_task_uq');
            $t->addIndex(['started', 'done'], 'tasks_st_done_idx');
        }

        // 4) Crea/aggiorna tabella mappa_executor_task
        if ($schema->hasTable('egonextapp_task_executors') && !$schema->hasTable('mappa_executor_task')) {
            $schema->renameTable('egonextapp_task_executors', 'mappa_executor_task');
        }
        if (!$schema->hasTable('mappa_executor_task')) {
            $t = $schema->createTable('mappa_executor_task');
            $t->addColumn('id', 'bigint', ['autoincrement' => true, 'notnull' => true]);
            $t->addColumn('taskname', 'string', ['length' => 64, 'notnull' => true]);
            $t->addColumn('mimetype', 'string', ['length' => 255, 'notnull' => true]);
            $t->addColumn('executor_class', 'string', ['length' => 255, 'notnull' => true]);
            $t->setPrimaryKey(['id']);
            $t->addUniqueIndex(['taskname', 'mimetype'], 'map_task_mime_uq');
        }

        return $schema;
    }

    /**
     * Post schema: se l'app "tables" è installata, crea VIEW di comodo
     * che puntano alle tabelle rinominate, per facilitarne l'uso in Tables.
     */
    public function postSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
        $conn = \OC::$server->getDatabaseConnection();
        $appManager = \OC::$server->getAppManager();

        $isTablesInstalled = $appManager->isInstalled('tables'); // true se presente
        if (!$isTablesInstalled) {
            return;
        }

        // prefisso (es. oc_)
        $prefix = $conn->getPrefix();

        // Crea VIEW se non esistono (idempotente)
        $views = [
            // view su coda
            "CREATE VIEW IF NOT EXISTS `{$prefix}tables_view_coda_nuovi_files` AS
             SELECT id, user_id, path, size, mimetype, mtime, FROM_UNIXTIME(created_at) AS created_at_dt, created_at
             FROM `{$prefix}coda_nuovi_files`",
            // view su tasks
            "CREATE VIEW IF NOT EXISTS `{$prefix}tables_view_tasks_attivi` AS
             SELECT id, path, taskname, started, done, started_at, done_at
             FROM `{$prefix}tasks_attivi`",
            // view su mappa executor
            "CREATE VIEW IF NOT EXISTS `{$prefix}tables_view_mappa_executor_task` AS
             SELECT id, taskname, mimetype, executor_class
             FROM `{$prefix}mappa_executor_task`",
        ];

        foreach ($views as $sql) {
            try {
                $conn->executeStatement($sql);
            } catch (\Throwable $e) {
                // Non bloccare l’upgrade
                \OC::$server->get(\Psr\Log\LoggerInterface::class)
                    ->warning('[egonextapp] Creazione VIEW per Tables fallita: '.$e->getMessage(), ['sql' => $sql]);
            }
        }
    }
}
