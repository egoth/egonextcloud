<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\Listener;

use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCA\Files\Event\LoadAdditionalScripts;
use OCP\Util;

/**
 * Listener chiamato quando la Files app carica script aggiuntivi.
 */
class FilesLoadAdditionalScriptsListener implements IEventListener {
	public function handle(Event $event): void {
		if (!($event instanceof LoadAdditionalScripts)) {
			return;
		}
		// include apps/egonextapp/js/main.js
		Util::addScript('egonextapp', 'main');
	}
}
