<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\BackgroundJob;

use OCA\EgoNextApp\Db\ActiveTaskMapper;
use Psr\Log\LoggerInterface;

class PdfPreviewExecutor extends BaseExecutor {
    public function __construct(ActiveTaskMapper $active, LoggerInterface $logger) {
        parent::__construct($active, $logger);
    }

    protected function doWork(string $path, string $taskname): void {
        // TODO: logica di elaborazione PDF (anteprime, ecc.)
        // simulazione:
        usleep(250000); // 250ms
        $this->logger->debug("[egonextapp] PdfPreviewExecutor processed $path");
    }
}
