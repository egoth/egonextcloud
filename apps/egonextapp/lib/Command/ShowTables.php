<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\Command;

use OCP\IDBConnection;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ShowTables extends Command
{
    protected static $defaultName = 'egonextapp:show-db';

    /**
     * @var array<string,string>
     */
    private array $tables = [
        'coda_nuovi_files' => 'Coda file registrati dagli hook',
        'tasks_attivi' => 'Tasks pendenti/avviati',
        'mappa_executor_task' => 'Mapping task -> executor',
    ];

    public function __construct(
        private IDBConnection $connection,
        private LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        try {
            $this->logger->info('[egonextapp] ShowTables::configure start');

            $this->setName(self::$defaultName);
            $this->setDescription('Mostra a console il contenuto delle tabelle usate da EgoNextCloud');
            $this->addOption(
                'limit',
                'l',
                InputOption::VALUE_OPTIONAL,
                'Numero massimo di righe da mostrare per tabella (0 = tutte)',
                '50'
            );
            $this->addOption(
                'table',
                't',
                InputOption::VALUE_OPTIONAL,
                'Nome tabella da mostrare (default: tutte)'
            );
        } catch (\Throwable $e) {
            $this->logger->error('[egonextapp] ShowTables::configure error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limitOpt = (string)$input->getOption('limit');
        $limit = (int)($limitOpt !== '' ? $limitOpt : '50');
        $tableFilter = (string)$input->getOption('table');

        $tables = $this->tables;
        if ($tableFilter !== '') {
            if (!isset($tables[$tableFilter])) {
                $output->writeln("<error>Tabella \"$tableFilter\" non riconosciuta.</error>");
                $output->writeln('Disponibili: ' . implode(', ', array_keys($tables)));
                return self::FAILURE;
            }
            $tables = [$tableFilter => $tables[$tableFilter]];
        }

        foreach ($tables as $tableName => $label) {
            $output->writeln('');
            $output->writeln(sprintf('<info>[%s] %s</info>', $tableName, $label));

            try {
                $rows = $this->fetchRows($tableName, $limit);
            } catch (\Throwable $e) {
                $msg = '[egonextapp] Errore lettura tabella: ' . $e->getMessage();
                $this->logger->error($msg, ['exception' => $e, 'table' => $tableName]);
                $output->writeln("<error>$msg</error>");
                continue;
            }

            if ($rows === []) {
                $output->writeln('  (nessun record)');
                continue;
            }

            $headers = array_keys($rows[0]);
            $table = new Table($output);
            $table->setHeaders($headers);
            foreach ($rows as $row) {
                $table->addRow(array_map(fn($value) => $this->stringify($value), $row));
            }
            $table->render();
        }

        return self::SUCCESS;
    }

    /**
     * @return array<int,array<string,mixed>>
     */
    private function fetchRows(string $table, int $limit): array
    {
        $prefixed = $this->getPrefixedTable($table);
        $qb = $this->connection->getQueryBuilder();
        $qb->select('*')
            ->from($prefixed)
            ->orderBy('id', 'ASC');

        if ($limit > 0) {
            $qb->setMaxResults($limit);
        }

        return $qb->executeQuery()->fetchAll();
    }

    private function stringify(mixed $value): string
    {
        if ($value === null) {
            return 'NULL';
        }
        if (is_bool($value)) {
            return $value ? '1' : '0';
        }
        if (is_scalar($value)) {
            return (string)$value;
        }

        return (string)json_encode($value, JSON_THROW_ON_ERROR);
    }

    private function getPrefixedTable(string $table): string
    {
        if (method_exists($this->connection, 'getPrefix')) {
            $prefix = (string)$this->connection->getPrefix();
            if ($prefix !== '') {
                return $prefix . $table;
            }
        }

        return $table;
    }
}
