<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\Listener;

use OCA\EgoNextApp\Service\CodaService;
use OCP\IUserSession;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Files\Events\Node\NodeCreatedEvent;
use OCP\Files\Events\Node\NodeWrittenEvent;
use Psr\Log\LoggerInterface;
use Throwable;

class FileEventsListener implements IEventListener {

    private CodaService $codaService;
    private IUserSession $userSession;
    private LoggerInterface $logger;

    public function __construct(
        CodaService $codaService,
        IUserSession $userSession,
        LoggerInterface $logger
    ) {
        $this->codaService = $codaService;
        $this->userSession = $userSession;
        $this->logger = $logger;
    }

    public function handle(Event $event): void {
        try {
            if ($event instanceof NodeCreatedEvent) {
                $node = $event->getNode();
                $user = $this->userSession->getUser()?->getUID() ?? 'anon';

                $this->logger->info("[egonextapp] File creato: {$node->getPath()} da {$user}");
                $this->codaService->addEntry($node, $user, 'created');
            }

            if ($event instanceof NodeWrittenEvent) {
                $node = $event->getNode();
                $user = $this->userSession->getUser()?->getUID() ?? 'anon';

                $this->logger->info("[egonextapp] File scritto/aggiornato: {$node->getPath()} da {$user}");
                $this->codaService->addEntry($node, $user, 'written');
            }
        } catch (Throwable $e) {
            // Non fermiamo mai l’evento, ma logghiamo l’errore
            $this->logger->error("[egonextapp] Errore nella gestione evento: " . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }
}
