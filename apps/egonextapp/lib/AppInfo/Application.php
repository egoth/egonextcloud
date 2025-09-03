
<?php
namespace OCA\EgoNextApp\AppInfo;
use OCP\AppFramework\App;

class Application extends App {
    public const APP_ID = "egonextapp";
    public function __construct(array $urlParams = []) {
        parent::__construct(self::APP_ID, $urlParams);
    }
}

