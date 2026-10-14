/* Retour à la position exacte de la page après une validation de formulaire.
   La position est mémorisée en sessionStorage juste avant l'envoi, puis
   restaurée au chargement de la page suivante. */

(function () {
  var KEY = "crm_scroll_pos";
  var KEY_FOCUS = "crm_last_field";

  function savePos() {
    try { sessionStorage.setItem(KEY, String(window.scrollY)); } catch (e) {}
  }

  // restauration au chargement de la page de retour
  window.addEventListener("load", function () {
    var pos, focusId;
    try { pos = sessionStorage.getItem(KEY); } catch (e) {}
    try { focusId = sessionStorage.getItem(KEY_FOCUS); } catch (e) {}
    if (pos !== null) {
      try { sessionStorage.removeItem(KEY); } catch (e) {}
      window.scrollTo(0, parseInt(pos, 10) || 0);
    }
    if (focusId) {
      try { sessionStorage.removeItem(KEY_FOCUS); } catch (e) {}
      var field = document.getElementById(focusId);
      if (field) { field.focus(); }
    }
  });

  // envoi manuel des formulaires (boutons VALIDER, Insérer, TRIER...)
  document.addEventListener("submit", savePos, true);

  // cas des champs auto-soumis (index_light.php : this.form.submit() dans onchange).
  // Un submit programmatique ne déclenche pas l'événement "submit", on surcharge
  // donc la méthode : position et champ actif sont sauvegardés avant chaque
  // submit() appelé en JavaScript.
  var nativeSubmit = HTMLFormElement.prototype.submit;
  HTMLFormElement.prototype.submit = function () {
    var field = document.activeElement && document.activeElement.id ? document.activeElement.id : "";
    if (field) {
      try { sessionStorage.setItem(KEY_FOCUS, field); } catch (e) {}
    }
    savePos();
    return nativeSubmit.apply(this, arguments);
  };

  // liens d'action type ARCHIVER / SUPPRIMER (navigation GET)
  document.addEventListener("click", function (e) {
    var a = e.target && e.target.closest ? e.target.closest("a") : null;
    if (a && /del_line\.php|del_def\.php/.test(a.getAttribute("href") || "")) {
      savePos();
    }
  }, true);
})();
