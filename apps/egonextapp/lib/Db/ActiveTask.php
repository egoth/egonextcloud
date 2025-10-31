<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\Db;

use OCP\AppFramework\Db\Entity;

/**
 * @method string getPath()
 * @method void   setPath(string $path)
 * @method string getTaskname()
 * @method void   setTaskname(string $taskname)
 * @method int    getStarted()
 * @method void   setStarted(int $started)
 * @method int    getDone()
 * @method void   setDone(int $done)
 * @method int    getStartedAt()
 * @method void   setStartedAt(int $ts)
 * @method int    getDoneAt()
 * @method void   setDoneAt(int $ts)
 */
class ActiveTask extends Entity {
    /** deve essere public */
    public $id;

    protected $path;
    protected $taskname;
    protected $started = 0;
    protected $done = 0;
    protected $startedAt = 0;
    protected $doneAt = 0;

    public function __construct() {
        $this->addType('started', 'integer');
        $this->addType('done', 'integer');
        $this->addType('startedAt', 'integer');
        $this->addType('doneAt', 'integer');
    }

    // IMPORTANTISSIMO: usa setter() per marcare i campi aggiornati
    public function setPath(string $v): void      { $this->setter('path', $v); }
    public function setTaskname(string $v): void  { $this->setter('taskname', $v); }
    public function setStarted(int $v): void      { $this->setter('started', $v); }
    public function setDone(int $v): void         { $this->setter('done', $v); }
    public function setStartedAt(int $v): void    { $this->setter('startedAt', $v); }
    public function setDoneAt(int $v): void       { $this->setter('doneAt', $v); }
}
