import type { Entry } from '@nextcloud/files'
import { addNewFileMenuEntry } from '@nextcloud/files'
import { t } from '@nextcloud/l10n'


console.info('[egonextapp] main.js caricato')

const myEntry: Entry = {
	 id: 'egonextapp-action',
    displayName: t('egonextapp', 'Ego Next Action'), // usa la traduzione NC
    iconSvgInline: `
        <svg width="16" height="16" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill="currentColor">
            <circle cx="8" cy="8" r="7" stroke="currentColor" stroke-width="2" fill="none"/>
            <text x="8" y="11" font-size="7" text-anchor="middle" fill="currentColor">E</text>
        </svg>
    `,
	handler(context: Folder, content: Node[]): void {


		// `context` is the current active folder
		// `content` is the content of the currently active folder
		// You can add new files here e.g. use the WebDAV functions to create files.
		// If new content is added, ensure to emit the event-bus signals so the files app can update the list.
        OC.dialogs.alert(
            `Hai cliccato `,
            'EgoNextApp'
        )
            
    }
}

addNewFileMenuEntry(myEntry)
