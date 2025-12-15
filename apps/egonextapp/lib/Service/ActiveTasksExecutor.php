<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\Service;

use OCA\EgoNextApp\Db\ActiveTaskMapper;
use OCA\EgoNextApp\Db\TaskExecutorMapMapper;
use OCA\EgoNextApp\Db\CodaMapper;
use OCA\EgoNextApp\BackgroundJob\BaseExecutor;
use OCP\AppFramework\Utility\ITimeFactory;
use Psr\Log\LoggerInterface;

class ActiveTasksExecutor
{
    public function __construct(
        private ActiveTaskMapper $active,
        private TaskExecutorMapMapper $map,
        private CodaMapper $coda,
        private LoggerInterface $logger,
        private ITimeFactory $timeFactory,
    ) {}

    /**
     * Cicla i task (path, taskname) non avviati e non conclusi,
     * risolve la classe executor e la esegue con timeout.
     */
    public function runPending(int $limit = 50): void
    {
        $rows = $this->active->findNotStartedNotDone($limit);
        foreach ($rows as $row) {
            $path = (string)($row['path'] ?? '');
            $taskname = (string)($row['taskname'] ?? '');
            if ($path === '' || $taskname === '') {
                continue;
            }

           

            $executorClass = $this->map->findExecutor($taskname);
            if (!$executorClass || !\class_exists($executorClass)) {
                $this->logger->warning("[egonextapp] Executor $executorClass non trovato per $taskname su $path");
                continue;
            }

            $executor = $this->instantiateExecutor($executorClass);
            if (!$executor instanceof BaseExecutor) {
                $this->logger->error("[egonextapp] Classe $executorClass non è un BaseExecutor");
                continue;
            }

            $this->runWithTimeout($executor, ['path' => $path, 'taskname' => $taskname]);
        }
    }

    private function instantiateExecutor(string $class): ?BaseExecutor
    {
        try {
            // I figli di BaseExecutor accettano (ITimeFactory, ActiveTaskMapper, LoggerInterface)
            return new $class($this->timeFactory, $this->active, $this->logger);
        } catch (\Throwable $e) {
            $this->logger->error('[egonextapp] Errore istanziando executor '.$class.': '.$e->getMessage());
            return null;
        }
    }

    private function runWithTimeout(BaseExecutor $executor, array $argument): void
    {
        $timeout = $this->resolveTimeout($executor);

        // Se disponibile, forka e uccide il figlio al timeout
        if (\function_exists('pcntl_fork') && \function_exists('pcntl_waitpid') && \function_exists('posix_kill')) {
            $pid = \pcntl_fork();
            if ($pid === -1) {
                $this->logger->warning('[egonextapp] fork fallito; eseguo inline');
                $this->runInlineWithSoftTimeout($executor, $argument, $timeout);
                return;
            }
            if ($pid === 0) {
                $this->resetDbConnection();
                // child
                $executor->runNow($argument);
                exit(0);
            }
            $this->resetDbConnection();

            $start = \time();
            while (true) {
                $res = \pcntl_waitpid($pid, $status, WNOHANG);
                if ($res === $pid) {
                    // terminato
                    break;
                }
                if ((\time() - $start) >= $timeout) {
                    \posix_kill($pid, SIGKILL);
                    $this->logger->warning('[egonextapp] Executor kill per timeout di '.$timeout.'s');
                    break;
                }
                \usleep(100000); // 100ms
            }
            return;
        }

        // Fallback: esecuzione inline, opzionale soft-timeout via alarm
        $this->runInlineWithSoftTimeout($executor, $argument, $timeout);
    }

    private function runInlineWithSoftTimeout(BaseExecutor $executor, array $argument, int $timeout): void
    {
        if (\function_exists('pcntl_signal') && \function_exists('pcntl_alarm')) {
            \pcntl_signal(SIGALRM, function () use ($executor) {
                throw new \RuntimeException('Executor timeout');
            });
            \pcntl_alarm($timeout);
            try {
                $executor->runNow($argument);
            } catch (\Throwable $e) {
                $this->logger->error('[egonextapp] Executor terminato per timeout/errore: '.$e->getMessage());
            } finally {
                \pcntl_alarm(0);
            }
        } else {
            // Nessun meccanismo hard-timeout disponibile
            $executor->runNow($argument);
        }
    }

    private function resolveTimeout(BaseExecutor $executor): int
    {
        $class = \get_class($executor);
        $const = $class.'::TIMEOUT_SECONDS';
        if (\defined($const)) {
            return (int) \constant($const);
        }
        return BaseExecutor::TIMEOUT_SECONDS;
    }

    private function resetDbConnection(): void
    {
        try {
            $this->active->resetConnection();
        } catch (\Throwable $e) {
            $this->logger->error('[egonextapp] Errore reset connessione DB: '.$e->getMessage());
        }
    }
}
