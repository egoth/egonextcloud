<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\Command;

use OCA\EgoNextApp\Service\ActiveTasksExecutor;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class RunActiveTasks extends Command
{
    protected static $defaultName = 'egonextapp:run-active-tasks';

    public function __construct(
        private ActiveTasksExecutor $runner,
        private LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        try {
            $this->logger->info('[egonextapp] RunActiveTasks::configure start');

            $this->setName('egonextapp:run-active-tasks'); // esplicito per compatibilità con Symfony usato da Nextcloud
            $this->logger->info('[egonextapp] RunActiveTasks::configure setName');

            $this->setDescription('Esegue i task attivi non avviati (started=0, done=0)');
            $this->logger->info('[egonextapp] RunActiveTasks::configure setDescription');

            $this->addOption('limit', 'l', InputOption::VALUE_OPTIONAL, 'Max task da processare', '50');
            $this->logger->info('[egonextapp] RunActiveTasks::configure addOption');
        } catch (\Throwable $e) {
            $this->logger->error('[egonextapp] RunActiveTasks::configure error: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            throw $e;
        }
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $limitOpt = (string)$input->getOption('limit');
        $limit = (int)($limitOpt !== '' ? $limitOpt : '50');
        $output->writeln("[egonextapp] Avvio runPending(limit=$limit)");

        try {
            $this->runner->runPending($limit);
            $output->writeln('[egonextapp] Completato.');
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $msg = '[egonextapp] Errore esecuzione: ' . $e->getMessage();
            $this->logger->error($msg, ['exception' => $e]);
            $output->writeln($msg);
            return self::FAILURE;
        }
    }
}
