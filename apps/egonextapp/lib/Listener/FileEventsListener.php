<?php
declare(strict_types=1);

namespace OCA\EgoNextApp\Listener;

use OC\Files\Filesystem;
use OC_App;
use OCA\EgoNextApp\Service\CodaService;
use OCP\IUserSession;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\Files\Events\Node\NodeCreatedEvent;
use OCP\Files\Events\Node\NodeWrittenEvent;
use OCP\Files\File;
use Psr\Log\LoggerInterface;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Throwable;

class FileEventsListener implements IEventListener {

    private const HEIC_TIMEOUT_SECONDS = 120;

    private CodaService $codaService;
    private IUserSession $userSession;
    private LoggerInterface $logger;
    private ?string $dataDirectory = null;
    private ?string $scriptPath = null;

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
                $this->codaService->enqueue($user,$node->getPath(),$node->getSize(),$node->getMimetype(),$node->getMTime());
                if ($node instanceof File) {
                    $this->maybeGenerateHeicPreviews($node);
                }
            } else if ($event instanceof NodeWrittenEvent) {
                $node = $event->getNode();
                $user = $this->userSession->getUser()?->getUID() ?? 'anon';

                $this->logger->info("[egonextapp] File scritto/aggiornato: {$node->getPath()} da {$user}");
                
                $this->codaService->enqueue($user,$node->getPath(),$node->getSize(),$node->getMimetype(),$node->getMTime());
                if ($node instanceof File) {
                    $this->maybeGenerateHeicPreviews($node);
                }
            }
        } catch (Throwable $e) {
            // Non fermiamo mai l’evento, ma logghiamo l’errore
            $this->logger->error("[egonextapp] Errore nella gestione evento: " . $e->getMessage(), [
                'exception' => $e,
            ]);
        }
    }

    private function maybeGenerateHeicPreviews(File $node): void {
        if ($node->getMimetype() !== 'image/heic') {
            return;
        }

        $heicPath = $this->resolveAbsoluteFilesystemPath($node->getPath());
        if ($heicPath === null) {
            $this->logger->warning('[egonextapp] Impossibile risolvere path HEIC per '.$node->getPath());
            return;
        }

        try {
            $previewDir = $this->buildPreviewBasePath($node->getId());
        } catch (Throwable $e) {
            $this->logger->error('[egonextapp] Impossibile costruire path anteprime: '.$e->getMessage());
            return;
        }

        if ($this->previewsAlreadyExist($previewDir)) {
            return;
        }

        $this->runHeicPreviewGenerator($heicPath, $previewDir);
    }

    private function previewsAlreadyExist(string $previewDir): bool {
        if (!is_dir($previewDir)) {
            return false;
        }

        $pngFiles = glob(rtrim($previewDir, '/').'/*.png') ?: [];
        return count($pngFiles) > 0;
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

    private function runHeicPreviewGenerator(string $heicPath, string $previewDir): void {
        $script = $this->getHeicPreviewScriptPath();
        if (!is_file($script)) {
            $this->logger->warning('[egonextapp] Script heic_previews non trovato: '.$script);
            return;
        }

        $process = new Process(['python3', $script, $heicPath, $previewDir]);
        $process->setTimeout(self::HEIC_TIMEOUT_SECONDS);

        try {
            $process->mustRun();
            $this->logger->info('[egonextapp] Anteprime HEIC generate', [
                'file' => $heicPath,
                'dir' => $previewDir,
                'output' => $process->getOutput(),
            ]);
        } catch (ProcessFailedException $e) {
            $this->logger->error('[egonextapp] Errore script HEIC: '.$e->getMessage(), [
                'stderr' => $process->getErrorOutput(),
            ]);
        }
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

    private function getHeicPreviewScriptPath(): string {
        if ($this->scriptPath === null) {
            $this->scriptPath = OC_App::getAppPath('egonextapp').'/bin/heic_previews.py';
        }

        return $this->scriptPath;
    }
}
