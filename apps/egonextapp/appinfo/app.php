<?php
/**
 * Carica lo script solo nell'app Files
 */
use OCP\Util;

\OCP\Util::connectHook('OCA\Files::loadAdditionalScripts', function () {
    Util::addScript('egonextapp', 'fileactions'); // carica apps/egonextapp/js/fileactions.js
    // Se vuoi anche uno stile:
    // Util::addStyle('egonextapp', 'fileactions');
});
