console.log("[egonextapp] main.js caricato correttamente");

// Quando la pagina è pronta
document.addEventListener("DOMContentLoaded", () => {
  console.log("[egonextapp] DOM caricato");

  // Registriamo una nuova azione nel menu file
  if (OC && OCA && OCA.Files && OCA.Files.fileActions) {
    console.log("[egonextapp] Registro azione nel menu contestuale");

    OCA.Files.fileActions.registerAction({
      name: 'EgoNextTest',
      displayName: t('egonextapp', 'Ego Next App'),
      mime: 'all',
      permissions: OC.PERMISSION_READ,
      iconClass: 'icon-add',
      actionHandler: function (filename, context) {
        alert("Hai cliccato su: " + filename);
      }
    });
  } else {
    console.error("[egonextapp] OCA.Files non è disponibile. Sei nella vista Files?");
  }
});
