<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\AppInfo;

use OCP\Util;

// log in nextcloud.log
\OC::$server->getLogger()->info('[egonextapp] app.php caricato');

// carica il JS
Util::addScript('egonextapp', 'main');
