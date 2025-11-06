<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\Migration;

use Closure;
use OCP\DB\ISchemaWrapper;
use OCP\IDBConnection;
use OCP\Migration\IOutput;
use OCP\Migration\SimpleMigrationStep;
use Psr\Log\LoggerInterface;

class Version0002Date20251031 extends SimpleMigrationStep {
    public function __construct(
        private IDBConnection $connection,
        private LoggerInterface $logger,
    ) {}

    /** Rinomina le vecchie tabelle con SQL diretto (portabile per i DB supportati) */
    public function preSchemaChange(IOutput $output, Closure $schemaClosure, array $options): void {
        /** @var IDBConnection $conn */
        $conn = $this->connection;
        
        $this->renameIfExistsSql($conn, 'egonextapp_coda',            'coda_nuovi_files');
        $this->renameIfExistsSql($conn, 'egonextapp_active_tasks',    'tasks_attivi');
        $this->renameIfExistsSql($conn, 'egonextapp_task_executors',  'mappa_executor_task');
    }

    /** Helper: tenta rename usando *PREFIX* senza introspezione schema */
    private function renameIfExistsSql(IDBConnection $conn, string $from, string $to): void {
        $platform = $conn->getDatabasePlatform()->getName();
        try {
            switch ($platform) {
                case 'mysql':
                    $sql = "RENAME TABLE `*PREFIX*{$from}` TO `*PREFIX*{$to}`";
                    break;
                case 'postgresql':
                    $sql = "ALTER TABLE IF EXISTS \"*PREFIX*{$from}\" RENAME TO \"*PREFIX*{$to}\"";
                    break;
                case 'sqlite':
                    $sql = "ALTER TABLE \"*PREFIX*{$from}\" RENAME TO \"*PREFIX*{$to}\"";
                    break;
                default:
                    $sql = "ALTER TABLE *PREFIX*{$from} RENAME TO *PREFIX*{$to}";
                    break;
            }
            $conn->executeStatement($sql);
        } catch (\Throwable $e) {
            $this->logger->debug(
                '[egonextapp] Rename ignorato: ' . $e->getMessage(),
                ['from' => $from, 'to' => $to]
            );
        }
    }

    

    public function changeSchema(IOutput $output, Closure $schemaClosure, array $options): ?ISchemaWrapper {
        /** @var ISchemaWrapper $schema */
        $schema = $schemaClosure();

        // coda_nuovi_files
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

        // tasks_attivi
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

        // mappa_executor_task
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

    /** Helper: rinomina tabella se esiste, con SQL corretto per il DB in uso */
    private function renameIfExists(IDBConnection $conn, string $prefix, string $from, string $to): void {
        return;
        $fromFull = $prefix . $from;
        $toFull   = $prefix . $to;

        // già rinominata?
        if ($schemaManager->tablesExist([$toFull])) {
            return;
        }
        if (!$schemaManager->tablesExist([$fromFull])) {
            return;
        }

        $platform = $conn->getDatabasePlatform()->getName(); // 'mysql', 'postgresql', 'sqlite'
        try {
            switch ($platform) {
                case 'mysql':
                    $sql = "RENAME TABLE `{$fromFull}` TO `{$toFull}`";
                    break;
                case 'postgresql':
                    $sql = "ALTER TABLE \"{$fromFull}\" RENAME TO \"{$toFull}\"";
                    break;
                case 'sqlite':
                    $sql = "ALTER TABLE \"{$fromFull}\" RENAME TO \"{$toFull}\"";
                    break;
                default:
                    // fallback: tentiamo sintassi ALTER TABLE ... RENAME TO ...
                    $sql = "ALTER TABLE {$fromFull} RENAME TO {$toFull}";
                    break;
            }
            $conn->executeStatement($sql);
        } catch (\Throwable $e) {
            $this->logger->warning(
                '[egonextapp] Rename fallito: ' . $e->getMessage(),
                ['from' => $fromFull, 'to' => $toFull]
            );
        }
    }
}
