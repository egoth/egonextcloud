// apps/egonextapp/src/main.js

import { registerFileAction } from '@nextcloud/files'

console.info('[egonextapp] main.js caricato')

// Registriamo una nuova azione
registerFileAction({
    id: 'egonextapp-action',
    displayName: t('egonextapp', 'Ego Next Action'), // usa la traduzione NC
    iconSvgInline: `
        <svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
            <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="2" fill="none"/>
            <text x="8" y="11" font-size="7" text-anchor="middle" fill="currentColor">E</text>
        </svg>
    `,
    exec: (fileInfo) => {
        OC.dialogs.alert(
            `Hai cliccato su: ${fileInfo.basename}`,
            'EgoNextApp'
        )
    },
    // sempre attiva (puoi filtrare per tipo file, ecc.)
    enabled: (fileInfo, view) => {
        return true
    },
})
