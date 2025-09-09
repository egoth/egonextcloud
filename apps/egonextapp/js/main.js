console.log('[egonextapp] main.js caricato');

// Aspetta che le file actions siano pronte
$(document).on('fileActionsReady', function (event, fileActions) {
    console.log('[egonextapp] fileActionsReady triggerato');

    fileActions.registerAction({
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
});
