<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

/** @extends QBMapper<ActiveTask> */
class ActiveTaskMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'tasks_attivi', ActiveTask::class);
    }

    /** Ritorna (path, taskname) non started & non done */
    public function findNotStartedNotDone(int $limit = 50): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('path', 'taskname')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('started', $qb->createNamedParameter(0)))
           ->andWhere($qb->expr()->eq('done', $qb->createNamedParameter(0)))
           ->setMaxResults($limit);
        return $qb->executeQuery()->fetchAll();
    }

    public function upsertIfMissing(string $path, string $taskname): void {
        // prova aggiornamento; se 0 righe, inserisci
        $qb = $this->db->getQueryBuilder();
        $qb->update($this->getTableName())
           ->set('path', $qb->createNamedParameter($path))
           ->set('taskname', $qb->createNamedParameter($taskname))
           ->where($qb->expr()->eq('path', $qb->createNamedParameter($path)))
           ->andWhere($qb->expr()->eq('taskname', $qb->createNamedParameter($taskname)));
        $cnt = $qb->executeStatement();
        if ($cnt === 0) {
            $e = new ActiveTask();
            $e->setPath($path);
            $e->setTaskname($taskname);
            $e->setStarted(0);
            $e->setDone(0);
            $this->insert($e);
        }
    }

    public function markStarted(string $path, string $taskname): void {
        $qb = $this->db->getQueryBuilder();
        $qb->update($this->getTableName())
           ->set('started', $qb->createNamedParameter(1))
           ->set('started_at', $qb->createNamedParameter(time()))
           ->where($qb->expr()->eq('path', $qb->createNamedParameter($path)))
           ->andWhere($qb->expr()->eq('taskname', $qb->createNamedParameter($taskname)))
           ->executeStatement();
    }

    public function markDone(string $path, string $taskname): void {
        $qb = $this->db->getQueryBuilder();
        $qb->update($this->getTableName())
           ->set('done', $qb->createNamedParameter(1))
           ->set('done_at', $qb->createNamedParameter(time()))
           ->where($qb->expr()->eq('path', $qb->createNamedParameter($path)))
           ->andWhere($qb->expr()->eq('taskname', $qb->createNamedParameter($taskname)))
           ->executeStatement();
    }
}
