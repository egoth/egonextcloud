import { addNewFileMenuEntry } from '@nextcloud/files'
import { t } from '@nextcloud/l10n'
import { registerFileAction } from '@nextcloud/files'

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


// Iconcina SVG inline (facoltativa)
const icon = `
<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"
     xmlns="http://www.w3.org/2000/svg">
  <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="2" fill="none"/>
  <text x="8" y="11" font-size="7" text-anchor="middle" fill="currentColor">E</text>
</svg>
`

// Definisci l’azione contestuale
registerFileAction({
  id: 'egonextapp-open',
  // etichetta nel menu; può essere stringa o funzione (nodes)=>string
  displayName: () => t('egonextapp', 'Ego Next Action'),
  // opzionale: icona
  iconSvgInline: `
<svg width="16" height="16" viewBox="0 0 16 16" fill="currentColor"
     xmlns="http://www.w3.org/2000/svg">
  <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="2" fill="none"/>
  <text x="8" y="11" font-size="7" text-anchor="middle" fill="currentColor">E</text>
</svg>
`,
  // quando mostrarla: qui SOLO per un singolo elemento e se è un file
  enabled: (nodes /* array di File/Folder */, _view) => {
    console.info('[egonextapp] file action abilitata')
    return true
    //return nodes.length === 1 && nodes[0].type === 'file'
  },
  // cosa fare al click
  exec: async (nodes, _view) => {
    const node = nodes[0]
    // semplice demo
    OC.dialogs.alert(`Hai cliccato: ${node.basename}`, 'EgoNextApp')
    // se poi modifichi qualcosa, puoi ricaricare la lista:
    // _view?.reload()
  },
})
console.info('[egonextapp] file action registrata')