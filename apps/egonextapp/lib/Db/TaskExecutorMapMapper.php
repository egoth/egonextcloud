<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

/** @extends QBMapper<TaskExecutorMap> */
class TaskExecutorMapMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        parent::__construct($db, 'egonextapp_task_executors', TaskExecutorMap::class);
    }

    public function findExecutor(string $taskname, string $mimetype): ?string {
        $qb = $this->db->getQueryBuilder();
        $qb->select('executor_class')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('taskname', $qb->createNamedParameter($taskname)))
           ->andWhere($qb->expr()->eq('mimetype', $qb->createNamedParameter($mimetype)))
           ->setMaxResults(1);
        $row = $qb->executeQuery()->fetchAssociative();
        return $row['executor_class'] ?? null;
    }
}
