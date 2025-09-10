<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\Service;

use OCA\EgoNextApp\Db\CodaEntry;
use OCA\EgoNextApp\Db\CodaMapper;

class CodaService {
    public function __construct(private CodaMapper $mapper) {}

    public function enqueue(string $userId, string $path, int $size, string $mimetype, int $mtime): void {
        $e = new CodaEntry();
        $e->setUserId($userId);
        $e->setPath($path);
        $e->setSize($size);
        $e->setMimetype($mimetype);
        $e->setMtime($mtime);
        $e->setCreatedAt(time());
        $this->mapper->insert($e);
    }
}
