console.log('[egonextapp] main.js caricato');

(function () {
    function registerAction() {
        if (typeof OCA === 'undefined' || typeof OCA.Files === 'undefined') {
            console.warn('[egonextapp] OCA.Files non è disponibile, riprovo...');
            setTimeout(registerAction, 500);
            return;
        }

        console.log('[egonextapp] Registro azione in OCA.Files');

        OCA.Files.fileActions.registerAction({
            name: 'EgoNextAppAction',
            displayName: t('egonextapp', 'Mostra messaggio'),
            mime: 'all',
            permissions: OC.PERMISSION_READ,
            iconClass: 'icon-info',
            actionHandler: function (fileName, context) {
                OC.dialogs.alert('Hai cliccato su: ' + fileName, 'Ego Next App');
            }
        });
    }

    document.addEventListener('DOMContentLoaded', registerAction);
})();
