console.log('[egonextapp] main.js caricato');

// attende caricamento pagina file
$(document).ready(function () {
    console.log('[egonextapp] document ready');

    if (OCA.Files && OCA.Files.fileActions) {
        console.log('[egonextapp] OCA.Files.fileActions disponibile');
    } else {
        console.error('[egonextapp] OCA.Files.fileActions NON disponibile');
    }
});
