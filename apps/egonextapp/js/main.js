(() => {
	console.info('[egonextapp] main.js loaded');

	const showToast = (msg) => {
		try {
			if (window.OCP?.Toast?.success) return OCP.Toast.success(msg);
			if (window.OC?.Notification?.showTemporary) return OC.Notification.showTemporary(msg);
		} catch(e){}
		alert(msg);
	};

	const waitForFiles = (deadline = Date.now() + 5000) => new Promise((resolve, reject) => {
		(function check() {
			if (window.OCA?.Files) return resolve(window.OCA.Files);
			if (Date.now() > deadline) return reject(new Error('OCA.Files non disponibile'));
			setTimeout(check, 100);
		})();
	});

	const registerAction = () => {
		const reg = window.OCA?.Files?.registerFileAction;
		if (typeof reg === 'function') {
			reg({
				id: 'egonextapp-action',
				displayName: "Di' ciao",
				iconSvgInline: '<svg viewBox="0 0 16 16" width="16" height="16" xmlns="http://www.w3.org/2000/svg"><text x="2" y="12">👋</text></svg>',
				order: 50,
				mimes: ['*'],
				enabled: () => true,
				actionHandler: (files) => {
					const names = (files || []).map(f => f.basename).join(', ') || '(nessuno)';
					showToast(`Ciao da EgoNextApp! Selezionati: ${names}`);
				},
			});
			console.info('[egonextapp] file action registrata (API moderna)');
			return;
		}
		// fallback legacy
		const fa = window.OCA?.Files?.fileActions;
		if (fa) {
			fa.registerAction({
				name: 'egonextapp-action',
				displayName: "Di' ciao",
				mime: 'all',
				permissions: window.OC?.PERMISSION_READ ?? 1,
				icon: () => '',
				actionHandler: (fileName) => showToast(`Ciao! ${fileName}`)
			});
			console.info('[egonextapp] file action registrata (legacy)');
		}
	};

	document.addEventListener('DOMContentLoaded', () => {
		waitForFiles().then(registerAction).catch(e => console.error('[egonextapp]', e.message));
	});
})();
