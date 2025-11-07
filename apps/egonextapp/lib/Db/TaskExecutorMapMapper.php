<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

/** @extends QBMapper<TaskExecutorMap> */
class TaskExecutorMapMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'mappa_executor_task', TaskExecutorMap::class);
    }

    public function findExecutor(string $taskname): ?string {
        $qb = $this->db->getQueryBuilder();
        $qb->select('executor_class')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('taskname', $qb->createNamedParameter($taskname)))
           ->setMaxResults(1);
        $row = $qb->executeQuery()->fetchAll();
        return $row['executor_class'] ?? null;
    }

    public function findTasknamesByMimetype(string $mimetype): array {
        $qb = $this->db->getQueryBuilder();
        $qb->select('executor_class')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('taskname', $qb->createNamedParameter($mimetype)))
           ->setMaxResults(100);
        $row = $qb->executeQuery()->fetchAll();
        return $row ?? null;
    }


}
