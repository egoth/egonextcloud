<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\Listener;

use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Files\Events\Node\NodeCreatedEvent;
use OCP\Files\Events\Node\NodeWrittenEvent;
use OCP\Files\FileInfo;
use OCP\IUserSession;
use OCA\EgoNextApp\Service\CodaService;

class FileEventsListener implements IEventListener {
    public function __construct(
        private CodaService $codaService,
        private IUserSession $userSession,
    ) {}

    public function handle(Event $event): void {
        if (!($event instanceof NodeCreatedEvent || $event instanceof NodeWrittenEvent)) {
            return;
        }

        $node = $event->getNode();
        if ($node->getType() !== FileInfo::TYPE_FILE) {
            return; // ignora cartelle
        }

        $user = $this->userSession->getUser();
        $userId = $user ? $user->getUID() : '';

        $path  = $node->getPath();
        $size  = (int)$node->getSize();
        $mime  = (string)$node->getMimetype();
        $mtime = (int)$node->getMTime();

        // Inserisci una riga in coda
        $this->codaService->enqueue($userId, $path, $size, $mime, $mtime);
    }
}
