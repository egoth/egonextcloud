<?php

declare(strict_types=1);

namespace OCA\EgoNextApp\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCA\EgoNextApp\Command\RunActiveTasks;
use OCA\EgoNextApp\Service\CodaService;
use OCA\EgoNextApp\Db\CodaMapper;
use OCA\EgoNextApp\Listener\FileEventsListener;
use Psr\Log\LoggerInterface;

class Application extends App implements IBootstrap
{
    public const APP_ID = 'egonextapp';

    private LoggerInterface $logger;

    public function __construct(array $urlParams = [])
    {
        parent::__construct(self::APP_ID, $urlParams);
        $this->logger = $this->getContainer()->get(LoggerInterface::class);
    }

    public function register(IRegistrationContext $context): void
    {
        $this->logger->info('[egonextapp] Application::register');

        // Servizi DB
        $context->registerService(CodaMapper::class, function ($c) {
            return new CodaMapper($c->get(\OCP\IDBConnection::class));
        });
        $context->registerService(CodaService::class, function ($c) {
            return new CodaService($c->get(CodaMapper::class));
        });

        // Listener eventi file
        $context->registerService(FileEventsListener::class, function ($c) {
            return new FileEventsListener(
                $c->get(CodaService::class),
                $c->get(\OCP\IUserSession::class),
                $c->get(LoggerInterface::class)
            );
        });

        $context->registerEventListener(\OCP\Files\Events\Node\NodeCreatedEvent::class, FileEventsListener::class);
        $context->registerEventListener(\OCP\Files\Events\Node\NodeWrittenEvent::class, FileEventsListener::class);

        $context->registerService(
            \OCA\EgoNextApp\Db\ActiveTaskMapper::class,
            fn($c) =>
            new \OCA\EgoNextApp\Db\ActiveTaskMapper($c->get(\OCP\IDBConnection::class))
        );
        $context->registerService(
            \OCA\EgoNextApp\Db\TaskExecutorMapMapper::class,
            fn($c) =>
            new \OCA\EgoNextApp\Db\TaskExecutorMapMapper($c->get(\OCP\IDBConnection::class))
        );
        $context->registerService(
            \OCA\EgoNextApp\Service\TaskOrchestrator::class,
            fn($c) =>
            new \OCA\EgoNextApp\Service\TaskOrchestrator(
                $c->get(\OCA\EgoNextApp\Db\CodaMapper::class),
                $c->get(\OCA\EgoNextApp\Db\ActiveTaskMapper::class),
                $c->get(\OCA\EgoNextApp\Db\TaskExecutorMapMapper::class),
                $c->get(\OCP\BackgroundJob\IJobList::class),
                $c->get(\Psr\Log\LoggerInterface::class),
            )
        );
        $context->registerService(\OCA\EgoNextApp\Service\ActiveTasksExecutor::class, function ($c) {
            return new \OCA\EgoNextApp\Service\ActiveTasksExecutor(
                $c->get(\OCA\EgoNextApp\Db\ActiveTaskMapper::class),
                $c->get(\OCA\EgoNextApp\Db\TaskExecutorMapMapper::class),
                $c->get(\OCA\EgoNextApp\Db\CodaMapper::class),
                $c->get(\Psr\Log\LoggerInterface::class)
            );
        });
        $context->registerService(RunActiveTasks::class, function ($c) {
            return new RunActiveTasks(
                $c->get(\OCA\EgoNextApp\Service\ActiveTasksExecutor::class),
                $c->get(\Psr\Log\LoggerInterface::class)
            );
        });
    }

    public function boot(IBootContext $context): void
    {
        $this->logger->info('[egonextapp] Application::boot');
        \OCP\Util::addScript(self::APP_ID, 'main');
    }
}
