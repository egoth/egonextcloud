<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\Listener;

use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Files\Events\Node\NodeCreatedEvent;
use OCP\Files\Events\Node\NodeWrittenEvent;
use OCP\Files\FileInfo;
use OCP\IUserSession;
use OCP\ILogger;
use OCA\EgoNextApp\Service\CodaService;

class FileEventsListener implements IEventListener {
    public function __construct(
        private CodaService $codaService,
        private IUserSession $userSession,
        private ILogger $logger
    ) {}

    public function handle(Event $event): void {
        try{

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

  // Log che il file è in upload/scritto
        $eventType = $event instanceof NodeCreatedEvent ? 'CREATED' : 'WRITTEN';
        $this->logger->info("[egonextapp] File {$eventType}: user={$userId}, path={$path}, size={$size}, mime={$mime}, mtime={$mtime}");



        // Inserisci una riga in coda
        $this->codaService->enqueue($userId, $path, $size, $mime, $mtime);


        $this->logger->debug("[egonextapp] File aggiunto in coda: {$path}");
         } catch (\Throwable $e) {
        // cattura qualunque eccezione o errore
        $this->logger->error(
            "[egonextapp] Errore durante la gestione evento file: " . $e->getMessage(),
            ['exception' => $e]
        );
    }
    }
}
