<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\AppInfo;

use OCP\Util;

// carica il JS "main.js" (senza estensione) in tutte le pagine
Util::addScript('egonextapp', 'main');
