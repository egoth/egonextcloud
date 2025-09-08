(function () {
  // Traduzione semplice
  const t = (s) => s;

  /**
   * API "legacy" ancora supportata in Files:
   * OCA.Files.fileActions.registerAction(...)
   * In molte installazioni Nextcloud 28–30 funziona regolarmente.
   */
  function registerLegacyAction() {
    if (!window.OCA || !OCA.Files || !OCA.Files.fileActions) {
      console.warn('egonextapp: FileActions API non disponibile');
      return;
    }

    OCA.Files.fileActions.registerAction({
      name: 'ego-action',
      displayName: t('Ego action'),
      mime: 'all',                   // valida per tutti i tipi di file
      permissions: OC.PERMISSION_READ, // basta il permesso di lettura
      iconClass: 'icon-info',        // icona standard di Nextcloud
      actionHandler: function (fileName, context) {
        // context contiene info utili (dir, fileid, etc.)
        const dir = context.dir || '';
        const fileid = context.$file && context.$file.data('id');
        OC.dialogs.alert(
          'Hai cliccato su: ' + fileName + '\nCartella: ' + dir + (fileid ? '\nfileid: ' + fileid : ''),
          'EgoNextApp'
        );
      }
    });

    // Posizionamento/ordinamento opzionale
    OCA.Files.fileActions.setDefault('file', 'ego-action');
    console.info('egonextapp: legacy file action registrata');
  }

  // Prova a registrare appena la pagina Files è pronta
  if (document.readyState === 'complete' || document.readyState === 'interactive') {
    registerLegacyAction();
  } else {
    document.addEventListener('DOMContentLoaded', registerLegacyAction);
  }
})();
