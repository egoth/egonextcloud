<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\EventDispatcher\IEventDispatcher; 
use OCP\Util;

class Application extends App implements IBootstrap {
    public const APP_ID = 'egonextapp';

    public function __construct() {
        parent::__construct(self::APP_ID);
    }

    public function register(IRegistrationContext $context): void {
        \OC::$server->getLogger()->info('[egonextapp] Application::register');
        
        
        //per registrazione upload
        $context->registerService(CodaMapper::class, function($c) {
            return new CodaMapper($c->get(IDBConnection::class));
        });
        $context->registerService(CodaService::class, function($c) {
            return new CodaService($c->get(CodaMapper::class));
        });
        $context->registerService(FileEventsListener::class, function($c) {
            return new FileEventsListener(
                $c->get(CodaService::class),
                $c->get(IUserSession::class),
            );
        });

        // Eventi file: nuovi file e scritture
        $context->registerEventListener(NodeCreatedEvent::class, FileEventsListener::class);
        $context->registerEventListener(NodeWrittenEvent::class, FileEventsListener::class);



    }

    public function boot(IBootContext $context): void {
        \OC::$server->getLogger()->info('[egonextapp] Application::boot');

        // carica lo script JS
        Util::addScript(self::APP_ID, 'main');

        $dispatcher = $context->getServerContainer()->get(IEventDispatcher::class);

        // Evento PRIMA dell’upload
        $dispatcher->addListener(BeforeFileScannedEvent::class, function(BeforeFileScannedEvent $event) {
            $file = $event->getFile();
            \OC::$server->getLogger()->info("[egonextapp] Inizio upload file: " . $file->getPath());
        });

        // Evento DOPO creazione nuovo file
        $dispatcher->addListener(FileCreatedEvent::class, function(FileCreatedEvent $event) {
            $file = $event->getFile();
            \OC::$server->getLogger()->info("[egonextapp] File creato: " . $file->getPath() . " (" . $file->getSize() . " bytes)");
        });

        // Evento DOPO scrittura (anche overwrite)
        $dispatcher->addListener(FileWrittenEvent::class, function(FileWrittenEvent $event) {
            $file = $event->getFile();
            \OC::$server->getLogger()->info("[egonextapp] File scritto/aggiornato: " . $file->getPath());
        });





    }
}
