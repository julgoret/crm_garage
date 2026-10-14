<?php
ini_set('display_errors', 0);
include_once('./connect_inc.php');
header('Content-Type: text/html; charset=utf-8');

// jour initial depuis l'URL : ?date=YYYY-MM-DD (ou plan/YYYY-MM-DD via rewrite)
$init_date = "";
if (isset($_GET['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date']) && strtotime($_GET['date']) !== false) {
  $init_date = $_GET['date'];
}
?><!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Plans journaliers — Garage Moderne</title>
<link rel="stylesheet" href="./admin.css">
<style>
  body { display: flex; height: 100vh; overflow: hidden; }
  aside { width: 320px; min-width: 320px; background: #fff; border-right: 1px solid var(--border); padding: 16px; overflow-y: auto; }
  .daybar { position: absolute; top: 0; left: 0; right: 0; background: #fff; border-bottom: 1px solid var(--border); padding: 10px 20px; display: flex; align-items: center; gap: 14px; z-index: 50; box-shadow: var(--shadow); }
  .daybar input[type="date"] { font-size: 15px; font-weight: 600; }
  #scene { flex: 1; position: relative; overflow: hidden; padding-top: 64px; }
  #svgWrap { position: absolute; inset: 64px 0 0 0; }
  #daySlider { flex: 1; }
  .daylabel { font-weight: 700; font-size: 16px; min-width: 200px; }
  .saved-dates-note { font-size: 11px; color: var(--text-muted); }
  .daybar .btn { padding: 7px 14px; font-size: 13px; }
  .status { font-size: 12px; margin-left: 8px; font-weight: 600; }
  .status.ok { color: var(--ok-accent, #2f9e44); }
  .status.err { color: var(--danger); }
  .status.pending { color: var(--text-muted); }
</style>
</head>
<body>

<div class="daybar">
  <span class="daylabel" id="dayLabel"></span>
  <button class="btn" id="dayPrev" title="Jour précédent">◀</button>
  <input type="range" id="daySlider" min="0" max="364" step="1">
  <button class="btn" id="dayNext" title="Jour suivant">▶</button>
  <input type="date" id="dayDate">
  <span class="status" id="saveStatus"></span>
</div>

<aside>
  <h1 style="margin:-16px -16px 4px; padding:14px 16px; font-size:17px;">Éditeur de plan du jour</h1>
  <p class="saved-dates-note">Le fond de plan est commun à tous les jours. Les blocs sont propres à chaque jour.</p>

  <h2>Zone sélectionnée</h2>
  <label>Nom</label>
  <input type="text" id="zName" placeholder="ex : Bar">
  <label>Couleur (hex)</label>
  <input type="color" id="zColor" value="#4da3ff">
  <button id="addRect">+ Nouveau bloc</button>
  <button id="delRect" class="danger">Supprimer</button>

  <h2>Actions</h2>
  <button id="savePlan" style="background:#2f9e44;">💾 Enregistrer le plan pour ce jour</button>
  <div style="margin-top:12px">
    <label>Copier le plan d'un autre jour</label>
    <input type="date" id="copyFrom">
    <button id="copyPlan" class="secondary">Répliquer depuis ce jour</button>
  </div>

  <p class="hint">
    Glisser un bloc pour le déplacer, attraper les poignées de coins pour le
    redimensionner. Utilise le curseur du haut (ou ◀ ▶) pour naviguer de jour
    en jour : les plans sauvegardés se chargent à la volée. Double-clic sur un
    bloc pour le renommer.
  </p>
</aside>

<div id="scene">
  <div id="svgWrap">
    <svg id="plan" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 700" preserveAspectRatio="xMidYMid meet"></svg>
  </div>
</div>

<script>window.PLAN_INIT_DATE = <?= json_encode($init_date) ?>;</script>
<script src="./plan_editor.js"></script>
</body>
</html>
