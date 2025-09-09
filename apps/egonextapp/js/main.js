console.log('[egonextapp] main.js caricato');

OC.Plugins.register('OCA.Files.FileList', {
    attach: function (fileList) {
        console.log('[egonextapp] attach su FileList');

        OCA.Files.FileActions.registerAction({
            id: 'egonextapp-action',
            displayName: t('egonextapp', 'Mostra messaggio'),
            iconClass: 'icon-info',
            mime: 'all',
            permissions: OC.PERMISSION_READ,
            actionHandler: (fileName, context) => {
                OC.dialogs.alert('Hai cliccato su: ' + fileName, 'Ego Next App');
            }
        });
    }
});
