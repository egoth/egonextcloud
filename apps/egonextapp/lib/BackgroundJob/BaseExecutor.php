<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\BackgroundJob;

use OCA\EgoNextApp\Db\ActiveTaskMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\Job;
use Psr\Log\LoggerInterface;

abstract class BaseExecutor extends Job {
    public const TIMEOUT_SECONDS = 60;
    public function __construct(
        ITimeFactory $timeFactory,
        protected ActiveTaskMapper $active,
        protected LoggerInterface $logger
    ) {
        parent::__construct($timeFactory);
    }

    /**
     * $argument = ['path' => ..., 'taskname' => ...]
     */
    protected function run($argument) : void {
        $path = $argument['path'] ?? '';
        $taskname = $argument['taskname'] ?? '';
        if ($path === '' || $taskname === '') {
            $this->logger->error('[egonextapp] Executor: argomenti mancanti', ['arg' => $argument]);
            return;
        }

        try {
            // “quando parte è un thread a parte” → mark started
            $this->active->markStarted($path, $taskname);
            $this->logger->info("[egonextapp] Executor start $taskname $path");

            // lavoro specifico del figlio
            $this->doWork($path, $taskname);

            // chiusura → mark done
            $this->active->markDone($path, $taskname);
            $this->logger->info("[egonextapp] Executor done  $taskname $path");
        } catch (\Throwable $e) {
            $this->logger->error("[egonextapp] Executor error $taskname $path: ".$e->getMessage(), ['exception'=>$e]);
            // opzionale: lasci started=1 e done=0 per retry; oppure marca done con stato errore se aggiungi una colonna status
        }
    }

    abstract protected function doWork(string $path, string $taskname): void;

    /**
     * Esegue immediatamente l'executor nello stesso processo, bypassando la coda dei Job.
     * Utile per orchestrazioni sincrone e test.
     */
    public function runNow(array $argument): void {
        $this->run($argument);
    }
}
