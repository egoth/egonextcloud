<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\AppInfo;

use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Bootstrap\IBootContext;

use OCA\Files\Event\LoadAdditionalScripts;
use OCA\EgoNextApp\Listener\FilesLoadAdditionalScriptsListener;

class Application extends App implements IBootstrap {
	public const APP_ID = 'egonextapp';

	public function __construct() {
		parent::__construct(self::APP_ID);
	}

	public function register(IRegistrationContext $context): void {
		// Quando si apre la Files app, fai caricare i tuoi JS
		$context->registerEventListener(
			LoadAdditionalScripts::class,
			FilesLoadAdditionalScriptsListener::class
		);
	}

	public function boot(IBootContext $context): void {
		// eventuale bootstrap runtime (non necessario qui)
	}
}
