<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\Command;

use OCA\EgoNextApp\Db\ActiveTaskMapper;
use OCA\EgoNextApp\Db\CodaMapper;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CleanupQueue extends Command {
    private const DEFAULT_BATCH_SIZE = 500;
    private const SECONDS_PER_DAY = 86400;

    protected static $defaultName = 'egonextapp:cleanup-queue';

    public function __construct(
        private CodaMapper $codaMapper,
        private ActiveTaskMapper $activeTaskMapper,
        private LoggerInterface $logger,
    ) {
        parent::__construct();
    }

    protected function configure(): void {
        $this->setName(self::$defaultName);
        $this->setDescription('Rimuove gli elementi in coda_nuovi_files più vecchi di N giorni cancellando i task collegati.');
        $this->addArgument(
            'days',
            InputArgument::REQUIRED,
            'Numero di giorni dopo i quali eliminare gli elementi in coda'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int {
        $daysArg = (int)$input->getArgument('days');
        if ($daysArg <= 0) {
            $output->writeln('<error>[egonextapp] Specificare un numero di giorni positivo.</error>');
            return self::INVALID;
        }

        $thresholdTimestamp = time() - ($daysArg * self::SECONDS_PER_DAY);
        $output->writeln(sprintf('[egonextapp] Pulizia elementi creati prima del %s', date('c', $thresholdTimestamp)));

        $totalQueueRemoved = 0;
        $totalTasksRemoved = 0;

        try {
            do {
                $batch = $this->codaMapper->findOlderThan($thresholdTimestamp, self::DEFAULT_BATCH_SIZE);
                $count = count($batch);
                if ($count === 0) {
                    break;
                }

                foreach ($batch as $row) {
                    $path = (string)$row['path'];
                    $id = (int)$row['id'];

                    $tasksDeleted = $this->activeTaskMapper->deleteByPath($path);
                    $totalTasksRemoved += $tasksDeleted;

                    $deletedQueue = $this->codaMapper->deleteById($id);
                    $totalQueueRemoved += $deletedQueue;

                    $this->logger->info('[egonextapp] CleanupQueue: eliminato elemento coda', [
                        'path' => $path,
                        'tasks_deleted' => $tasksDeleted,
                        'queue_deleted' => $deletedQueue,
                    ]);
                }
            } while ($count === self::DEFAULT_BATCH_SIZE);

            $output->writeln(sprintf(
                '[egonextapp] Pulizia completata: rimossi %d task_attivi e %d record in coda.',
                $totalTasksRemoved,
                $totalQueueRemoved
            ));
            return self::SUCCESS;
        } catch (\Throwable $e) {
            $message = '[egonextapp] Errore durante cleanup: '.$e->getMessage();
            $this->logger->error($message, ['exception' => $e]);
            $output->writeln("<error>$message</error>");
            return self::FAILURE;
        }
    }
}
