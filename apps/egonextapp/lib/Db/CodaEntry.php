<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getUserId()
 * @method string getPath()
 * @method int    getSize()
 * @method string getMimetype()
 * @method int    getMtime()
 * @method int    getCreatedAt()
 */
class CodaEntry extends Entity {
    public $id;

    protected $userId;
    protected $path;
    protected $size = 0;
    protected $mimetype = '';
    protected $mtime = 0;
    protected $createdAt = 0;

    public function __construct() {
        $this->addType('size', 'integer');
        $this->addType('mtime', 'integer');
        $this->addType('createdAt', 'integer');
    }

  
}
