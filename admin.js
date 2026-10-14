/* Retour à la position exacte de la page après une validation de formulaire.
   La position est mémorisée en sessionStorage juste avant l'envoi, puis
   restaurée au chargement de la page suivante. */

(function () {
  var KEY = "crm_scroll_pos";

  function savePos() {
    try { sessionStorage.setItem(KEY, String(window.scrollY)); } catch (e) {}
  }

  window.addEventListener("load", function () {
    var pos;
    try { pos = sessionStorage.getItem(KEY); } catch (e) {}
    if (pos !== null) {
      try { sessionStorage.removeItem(KEY); } catch (e) {}
      window.scrollTo(0, parseInt(pos, 10) || 0);
    }
  });

  // envoi des formulaires (VALIDER, Insérer, TRIER, VALIDER LES CHANGEMENTS...)
  document.addEventListener("submit", savePos, true);

  // liens d'action type ARCHIVER / SUPPRIMER (navigation GET)
  document.addEventListener("click", function (e) {
    var a = e.target && e.target.closest ? e.target.closest("a") : null;
    if (a && /del_line\.php|del_def\.php/.test(a.getAttribute("href") || "")) {
      savePos();
    }
  }, true);
})();
