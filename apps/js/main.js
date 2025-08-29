// Compat: Nextcloud 28–30
// Proviamo prima l'API moderna, poi facciamo fallback alla legacy.

(async () => {
	// Messaggio di notifica cross-version
	const showToast = (msg) => {
		if (window.OCP && OCP.Toast && OCP.Toast.success) {
			OCP.Toast.success(msg)
		} else if (window.OC && OC.Notification && OC.Notification.showTemporary) {
			OC.Notification.showTemporary(msg)
		} else {
			alert(msg)
		}
	}

	// ---- API moderna (Nextcloud >= 25): @nextcloud/files (se presente) ----
	try {
		// dinamico: l'oggetto globale è esposto in window.OCA?.Files?.registerFileAction
		const registerFileAction = window.OCA?.Files?.registerFileAction
		if (typeof registerFileAction === 'function') {
			registerFileAction({
				id: 'egonextapp-action',
				displayName: t('egonextapp', 'Di\' ciao'),
				iconSvgInline: '<svg viewBox="0 0 16 16" width="16" height="16" xmlns="http://www.w3.org/2000/svg"><text x="2" y="12">👋</text></svg>',
				order: 50,
				enabled: (node, view) => true,              // sempre visibile
				mimes: ['*'],                               // su tutti i tipi di file/cartelle
				actionHandler: (files, view) => {
					// files: array di voci selezionate
					const names = files.map(f => f.basename).join(', ')
					showToast(`Ciao da EgoNextApp! Elementi selezionati: ${names || '(nessuno)'}`)
				},
			})
			console.info('[egonextapp] File action registrata (API moderna)')
			return
		}
	} catch (e) {
		console.warn('[egonextapp] API moderna non disponibile:', e)
	}

	// ---- Fallback legacy (OCA.Files.fileActions) ----
	if (window.OCA && OCA.Files && OCA.Files.fileActions) {
		const actions = OCA.Files.fileActions
		const actionName = 'egonextapp-action'

		// icona semplice (usiamo un carattere; opzionalmente puoi servire un'icona)
		const action = {
			name: actionName,
			displayName: t ? t('egonextapp', 'Di\' ciao') : "Di' ciao",
			mime: 'all',
			permissions: OC.PERMISSION_READ,
			icon: () => '', // niente icona legacy
			actionHandler: (fileName, context) => {
				showToast(`Ciao da EgoNextApp! Hai cliccato su: ${fileName}`)
			},
		}
		actions.registerAction(action)
		actions.setDefault('all', actionName, false) // non default, solo nel menu
		console.info('[egonextapp] File action registrata (API legacy)')
	} else {
		console.warn('[egonextapp] API Files non trovata; impossibile registrare l\'azione.')
	}
})()
