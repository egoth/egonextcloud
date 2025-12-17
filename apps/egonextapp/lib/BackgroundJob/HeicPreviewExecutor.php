<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\BackgroundJob;

use OCA\EgoNextApp\Db\ActiveTaskMapper;
use OC\Files\Filesystem;
use OC_App;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Files\File;
use OCP\Files\IRootFolder;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class HeicPreviewExecutor extends BaseExecutor {
    private ?IRootFolder $rootFolder = null;
    private ?string $dataDirectory = null;
    private ?string $scriptPath = null;

    public function __construct(ITimeFactory $timeFactory, ActiveTaskMapper $active, LoggerInterface $logger) {
        parent::__construct($timeFactory, $active, $logger);
    }

    protected function doWork(string $path, string $taskname): void {
        $fileNode = $this->resolveFileNode($path);
        if ($fileNode === null) {
            return;
        }

        $heicPath = $this->resolveAbsoluteFilesystemPath($path);
        if ($heicPath === null) {
            $this->logger->warning("[egonextapp] File HEIC non trovato su FS locale: $path");
            return;
        }

        $previewDir = $this->buildPreviewBasePath($fileNode->getId());
        $this->runGenerator($heicPath, $previewDir);
    }

    private function resolveFileNode(string $path): ?File {
        try {
            $node = $this->getRootFolder()->get($path);
            if (!$node instanceof File) {
                $this->logger->warning("[egonextapp] Nodo non è un file: $path");
                return null;
            }
            return $node;
        } catch (\Throwable $e) {
            $this->logger->error("[egonextapp] Impossibile risolvere il nodo $path: ".$e->getMessage());
            return null;
        }
    }

    private function resolveAbsoluteFilesystemPath(string $nextcloudPath): ?string {
        $relative = ltrim(Filesystem::normalizePath($nextcloudPath), '/');
        if ($relative === '' || str_starts_with($relative, '../')) {
            return null;
        }

        $candidate = $this->getDataDirectory().'/'.$relative;
        return is_file($candidate) ? $candidate : null;
    }

    private function buildPreviewBasePath(int $fileId): string {
        $config = \OC::$server->getConfig();
        $instanceId = (string)$config->getSystemValue('instanceid', '');
        if ($instanceId === '') {
            throw new \RuntimeException('Instance ID non configurato');
        }

        return $this->getDataDirectory().'/appdata_'.$instanceId.'/preview/'.$fileId;
    }

    private function runGenerator(string $heicPath, string $previewDir): void {
        $script = $this->getScriptPath();
        if (!is_file($script)) {
            throw new \RuntimeException("Script heic_previews non trovato: $script");
        }

        $process = new Process(['python3', $script, $heicPath, $previewDir]);
        $process->setTimeout(self::TIMEOUT_SECONDS);

        try {
            $process->mustRun();
            $this->logger->info(
                '[egonextapp] Anteprime HEIC generate',
                ['file' => $heicPath, 'dir' => $previewDir, 'output' => $process->getOutput()]
            );
        } catch (ProcessFailedException $e) {
            $this->logger->error(
                '[egonextapp] Errore esecuzione script HEIC: '.$e->getMessage(),
                ['stderr' => $process->getErrorOutput()]
            );
            throw $e;
        }
    }

    private function getRootFolder(): IRootFolder {
        if ($this->rootFolder === null) {
            $this->rootFolder = \OC::$server->get(IRootFolder::class);
        }
        return $this->rootFolder;
    }

    private function getDataDirectory(): string {
        if ($this->dataDirectory === null) {
            $config = \OC::$server->getConfig();
            $this->dataDirectory = rtrim(
                (string)$config->getSystemValue('datadirectory', \OC::$SERVERROOT.'/data'),
                '/'
            );
        }
        return $this->dataDirectory;
    }

    private function getScriptPath(): string {
        if ($this->scriptPath === null) {
            $this->scriptPath = OC_App::getAppPath('egonextapp').'/bin/heic_previews.py';
        }
        return $this->scriptPath;
    }
}
