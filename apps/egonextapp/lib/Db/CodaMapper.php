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
}
