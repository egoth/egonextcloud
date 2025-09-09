import { registerFileAction } from '@nextcloud/files'
import { showInfo } from '@nextcloud/dialogs'
import { translate as t } from '@nextcloud/l10n'

console.log('[egonextapp] main.js caricato')

// registra azione nel menu contestuale dei file
registerFileAction({
    id: 'egonextapp-action',
    displayName: t('egonextapp', 'Mostra messaggio'),
    icon: 'icon-info',
    enabled: (nodes, view) => true,
    exec: async (nodes, view) => {
        const fileNames = nodes.map(node => node.basename).join(', ')
        showInfo(`Hai cliccato su: ${fileNames}`)
    },
})
