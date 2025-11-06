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
     * 1) Trova i (path, mimetype) presenti in coda_nuovi_files che NON hanno righe
     *    corrispondenti in tasks_attivi (per qualsiasi task).
     * 2) Per ciascun (path, mimetype) risolve i taskname dalla mappa (mappa_executor_task)
     *    e inserisce in tasks_attivi i record mancanti con started=0, done=0.
     */
    public function dispatchPending(array $unused = [], int $limit = 500): void {
        try {
            $candidates = $this->coda->findPathsAndMimetypeNotInActiveTasks($limit);
            foreach ($candidates as $row) {
                $path = $row['path'] ?? '';
                $mimetype = $row['mimetype'] ?? '';
                if ($path === '' || $mimetype === '') {
                    continue;
                }

                $tasknames = $this->map->findTasknamesByMimetype($mimetype);
                foreach ($tasknames as $taskname) {
                    $this->active->upsertIfMissing($path, $taskname);
                    $this->logger->info("[egonextapp] tasks_attivi + ($taskname, $path) [mimetype=$mimetype]");
                }
            }
        } catch (\Throwable $e) {
            $this->logger->error('[egonextapp] Orchestrator error: '.$e->getMessage(), ['exception' => $e]);
        }
    }
}
