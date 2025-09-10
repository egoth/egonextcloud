<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\Db;

use OCP\AppFramework\Db\Entity;

class CodaEntry extends Entity {
    /** @var int|null */
    protected $id;
    /** @var string */
    protected $userId;
    /** @var string */
    protected $path;
    /** @var int */
    protected $size = 0;
    /** @var string */
    protected $mimetype = '';
    /** @var int */
    protected $mtime = 0;
    /** @var int */
    protected $createdAt = 0;

    public function __construct() {
        $this->addType('id', 'integer');
        $this->addType('size', 'integer');
        $this->addType('mtime', 'integer');
        $this->addType('createdAt', 'integer');
    }

    // Getter/Setter minimi
    public function getUserId(): string { return $this->userId; }
    public function setUserId(string $v): void { $this->userId = $v; }

    public function getPath(): string { return $this->path; }
    public function setPath(string $v): void { $this->path = $v; }

    public function getSize(): int { return $this->size; }
    public function setSize(int $v): void { $this->size = $v; }

    public function getMimetype(): string { return $this->mimetype; }
    public function setMimetype(string $v): void { $this->mimetype = $v; }

    public function getMtime(): int { return $this->mtime; }
    public function setMtime(int $v): void { $this->mtime = $v; }

    public function getCreatedAt(): int { return $this->createdAt; }
    public function setCreatedAt(int $v): void { $this->createdAt = $v; }
}
