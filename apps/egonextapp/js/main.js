console.log('[egonextapp] main.js caricato');

(function () {
    function registerAction() {
        if (typeof OCA === 'undefined' || !OCA.Files || !OCA.Files.FileActions) {
            console.warn('[egonextapp] OCA.Files.FileActions non è disponibile, riprovo...');
            setTimeout(registerAction, 500);
            return;
        }

        console.log('[egonextapp] Registro azione con FileActions');

        OCA.Files.FileActions.registerAction({
            id: 'egonextapp-action',
            displayName: t('egonextapp', 'Mostra messaggio'),
            iconClass: 'icon-info',
            mime: 'all',
            permissions: OC.PERMISSION_READ,
            actionHandler: (fileName, context) => {
                OC.dialogs.alert(
                    'Hai cliccato su: ' + fileName,
                    'Ego Next App'
                );
            }
        });
    }

    document.addEventListener('DOMContentLoaded', registerAction);
})();
