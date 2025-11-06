<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

/** @extends QBMapper<ActiveTask> */
class ActiveTaskMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'egonextapp_active_tasks', ActiveTask::class);
    }

    /** Ritorna (path, taskname) non started & non done */
    public function findNotStartedNotDone(int $limit = 50): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('path', 'taskname')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('started', $qb->createNamedParameter(0)))
           ->andWhere($qb->expr()->eq('done', $qb->createNamedParameter(0)))
           ->setMaxResults($limit);
        return $qb->executeQuery()->fetchAllAssociative();
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

    /**
     * Restituisce i path presenti in coda_nuovi_files per uno specifico mimetype
     * che non hanno ancora una riga corrispondente in active_tasks per il taskname dato.
     */
    public function findPathsForMappingNotEnqueued(string $taskname, string $mimetype, int $limit = 500): array {
        $qb = $this->db->getQueryBuilder();
        $activeTable = $this->getTableName();

        $qb->selectDistinct('c.path')
           ->from('coda_nuovi_files', 'c')
           ->leftJoin(
               'c',
               $activeTable,
               't',
               't.path = c.path AND t.taskname = ' . $qb->createNamedParameter($taskname)
           )
           ->where($qb->expr()->eq('c.mimetype', $qb->createNamedParameter($mimetype)))
           ->andWhere($qb->expr()->isNull('t.id'))
           ->setMaxResults($limit);

        return $qb->executeQuery()->fetchFirstColumn();
    }
}
