<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrapContext;
use OCP\Util;

class Application extends App implements IBootstrap {
    public const APP_ID = 'egonextapp';

    public function __construct() {
        parent::__construct(self::APP_ID);
    }

    public function register(IBootContext $context): void {
        \OC::$server->getLogger()->info('[egonextapp] Application::register');
    }

    public function boot(IBootstrapContext $context): void {
        \OC::$server->getLogger()->info('[egonextapp] Application::boot');

        // Carica il JS in tutte le pagine
        Util::addScript(self::APP_ID, 'main');
    }
}
