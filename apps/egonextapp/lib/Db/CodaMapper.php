<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\Db;

use OCP\AppFramework\Db\QBMapper;
use OCP\IDBConnection;

class CodaMapper extends QBMapper {
    public function __construct(IDBConnection $db) {
        // nome tabella SENZA prefisso (Nextcloud aggiunge il prefisso, es. "oc_")
        parent::__construct($db, 'coda_nuovi_files', CodaEntry::class);
    }

    /**
     * Restituisce elenco di [path, mimetype] presenti in coda_nuovi_files
     * che non hanno ancora alcuna riga corrispondente in egonextapp_active_tasks.
     */
    public function findPathsAndMimetypeNotInActiveTasks(int $limit = 500): array {
        $qb = $this->db->getQueryBuilder();
        $activeTable = 'tasks_attivi';

        $qb->selectDistinct(['c.path', 'c.mimetype'])
           ->from($this->getTableName(), 'c')
           ->leftJoin('c', $activeTable, 't', 't.path = c.path')
           ->where($qb->expr()->isNull('t.id'))
           ->setMaxResults($limit);

        return $qb->executeQuery()->fetchAll();
    }

    /**
     * Restituisce l'ultimo mimetype noto per un dato path
     * presente in coda_nuovi_files, oppure null se assente.
     */
    public function findMimetypeByPath(string $path): ?string {
        $qb = $this->db->getQueryBuilder();
        $qb->select('mimetype')
           ->from($this->getTableName())
           ->where($qb->expr()->eq('path', $qb->createNamedParameter($path)))
           ->orderBy('created_at', 'DESC')
           ->setMaxResults(1);

        $val = $qb->executeQuery()->fetchOne();
        return ($val === false) ? null : (string)$val;
    }
}
