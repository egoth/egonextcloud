<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\BackgroundJob;

use OCA\EgoNextApp\Db\ActiveTaskMapper;
use OCP\AppFramework\Utility\ITimeFactory;
use Psr\Log\LoggerInterface;

class PdfPreviewExecutor extends BaseExecutor {
    public function __construct(ITimeFactory $timeFactory, ActiveTaskMapper $active, LoggerInterface $logger) {
        parent::__construct($timeFactory, $active, $logger);
    }

    protected function doWork(string $path, string $taskname): void {
        // TODO: logica di elaborazione Heic (anteprime, ecc.)
        // simulazione:
        usleep(250000); // 250ms
        $this->logger->debug("[egonextapp] PdfPreviewExecutor processed $path");
    }
}
