<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getTaskname()
 * @method void   setTaskname(string $v)
 * @method string getMimetype()
 * @method void   setMimetype(string $v)
 * @method string getExecutorClass()
 * @method void   setExecutorClass(string $v)
 */
class TaskExecutorMap extends Entity {
    public $id;
    protected $taskname;
    protected $mimetype;
    protected $executorClass;

    public function setTaskname(string $v): void     { $this->setter('taskname', [$v]); }
    public function setMimetype(string $v): void     { $this->setter('mimetype', [$v]); }
    public function setExecutorClass(string $v): void{ $this->setter('executorClass', [$v]); }
}
