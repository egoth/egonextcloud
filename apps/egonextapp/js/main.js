console.log('[egonextapp] main.js caricato');

// Registriamo il plugin per la FileList
OC.Plugins.register('OCA.Files.FileList', {
    attach: function (fileList) {
        console.log('[egonextapp] attach eseguito');

        fileList.fileActions.registerAction({
            id: 'egonextapp-action',
            displayName: t('egonextapp', 'Mostra messaggio'),
            iconClass: 'icon-info',
            mime: 'all',
            permissions: OC.PERMISSION_READ,
            actionHandler: (fileName, context) => {
                console.log('[egonextapp] azione eseguita su', fileName);
                OC.dialogs.alert('Hai cliccato su: ' + fileName, 'Ego Next App');
            }
        });
    }
});
