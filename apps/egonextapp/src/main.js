import { addNewFileMenuEntry } from '@nextcloud/files'
import { t } from '@nextcloud/l10n'

console.info('[egonextapp] main.js caricato')

const myEntry = {
    id: 'egonextapp-action',
    displayName: t('egonextapp', 'Ego Next Action'),
    iconSvgInline: `
        <svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
            <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="2" fill="none"/>
            <text x="8" y="11" font-size="7" text-anchor="middle" fill="currentColor">E</text>
        </svg>
    `,
    handler(context, content) {
        OC.dialogs.alert(
            `Hai cliccato nella cartella ${context.basename}`,
            'EgoNextApp'
        )
    }
}

console.info('[egonextapp] sto per aggiungere')
try{
    addNewFileMenuEntry(myEntry)
    console.info('[egonextapp] aggiunto')
}catch(e){
    console.info('[egonextapp] errore')
}
