<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\Service;

use OCA\EgoNextApp\Db\ActiveTaskMapper;
use OCA\EgoNextApp\Db\TaskExecutorMapMapper;
use OCA\EgoNextApp\Db\CodaMapper; // esistente nel tuo progetto
use OCP\BackgroundJob\IJobList;
use Psr\Log\LoggerInterface;

class TaskOrchestrator {
    public function __construct(
        private CodaMapper $coda,
        private ActiveTaskMapper $active,
        private TaskExecutorMapMapper $map,
        private IJobList $jobs,
        private LoggerInterface $logger,
    ) {}

    /**
     * 1) prende i path dalla coda
     * 2) per ogni taskname previsto, upsert su active_tasks
     * 3) sceglie le righe not started & not done
     * 4) risolve l'executor e enqueua un background job (thread-like)
     */
    public function dispatchPending(array $tasknames, int $limit = 50): void {
        try {
            // 1) prendo paths + mimetype dalla coda
            $items = $this->coda->findLatestPathsWithMimetype($limit); // implementa nel tuo CodaMapper
            foreach ($items as $it) {
                $path = $it['path'];
                $mimetype = $it['mimetype'] ?? '';
                foreach ($tasknames as $taskname) {
                    // 2) upsert active record
                    $this->active->upsertIfMissing($path, $taskname);
                }
            }

            // 3) seleziona non started e non done
            $todo = $this->active->findNotStartedNotDone($limit);
            foreach ($todo as $row) {
                $path = $row['path'];
                $taskname = $row['taskname'];
                // recupero mimetype dal record di coda (metodo di supporto)
                $mimetype = $this->coda->findMimetypeByPath($path) ?? '';

                // 4) risolvo executor class
                $executorClass = $this->map->findExecutor($taskname, $mimetype);
                if (!$executorClass) {
                    $this->logger->warning("[egonextapp] Nessun executor per task=$taskname mimetype=$mimetype path=$path");
                    continue;
                }

                // enqueue background job
                $this->jobs->add($executorClass, [
                    'path' => $path,
                    'taskname' => $taskname,
                ]);
                $this->logger->info("[egonextapp] Enqueued $executorClass for $taskname $path");
            }
        } catch (\Throwable $e) {
            $this->logger->error('[egonextapp] Orchestrator error: '.$e->getMessage(), ['exception' => $e]);
        }
    }
}
