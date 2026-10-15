<?php
ini_set('display_errors', 0);
include_once('./connect_inc.php');
header('Content-Type: text/html; charset=utf-8');

// jour initial depuis l'URL : ?date=YYYY-MM-DD
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
  html, body { height: 100%; }
  body { margin: 0; display: flex; flex-direction: column; overflow: hidden; }

  /* barre fixe : navigation de jour + configuration des blocs */
  #topbar {
    position: fixed; top: 0; left: 0; right: 0; z-index: 50;
    background: #fff; box-shadow: var(--shadow);
    display: flex; flex-direction: column;
  }
  #daybar, #blockbar {
    display: flex; align-items: center; gap: 12px;
    padding: 8px 20px;
  }
  #daybar { border-bottom: 1px solid var(--border); }
  #blockbar { border-bottom: 1px solid var(--border); flex-wrap: wrap; }

  .daylabel { font-weight: 700; font-size: 15px; min-width: 210px; }
  #daybar input[type="date"] { font-size: 14px; font-weight: 600; }
  #daySlider { flex: 1; }

  .field { display: flex; align-items: center; gap: 6px; }
  .field label { font-size: 12px; color: var(--text-muted); white-space: nowrap; }
  .sep { width: 1px; height: 26px; background: var(--border); }

  .status { font-size: 12px; font-weight: 600; }
  .status.ok { color: #2f9e44; }
  .status.err { color: var(--danger); }
  .status.pending { color: var(--text-muted); }

  button {
    background: var(--accent); color: #fff; border: none; border-radius: 6px;
    padding: 7px 14px; font-size: 13px; font-weight: 600; cursor: pointer;
  }
  button:hover { filter: brightness(.9); }
  button.secondary { background: #e7edf4; color: var(--text); }
  button.danger { background: var(--danger); }
  #savePlan { background: #2f9e44; }

  input[type="text"], input[type="date"], input[type="color"], select {
    padding: 6px 8px; border: 1px solid var(--border); border-radius: 6px;
    font-size: 13px; background: #fff; max-width: 100%;
  }
  input:disabled { opacity: .45; }
  #zName { width: 180px; }
  #zColor { height: 32px; padding: 2px; cursor: pointer; }
  #copyFrom { font-size: 12px; }

  /* la scène occupe tout l'espace sous la barre fixe */
  #scene { position: fixed; left: 0; right: 0; bottom: 0; }
  #svgWrap { position: absolute; left: 0; right: 0; top: 0; bottom: 0; }
  svg { width: 100%; height: 100%; display: block; }
</style>
</head>
<body>

<div id="topbar">
  <div id="daybar">
    <span class="daylabel" id="dayLabel"></span>
    <button id="dayPrev" title="Jour précédent">◀</button>
    <input type="range" id="daySlider" min="0" max="364" step="1">
    <button id="dayNext" title="Jour suivant">▶</button>
    <input type="date" id="dayDate">
    <span class="status" id="saveStatus"></span>
  </div>
  <div id="blockbar">
    <span class="field"><label>Bloc</label><input type="text" id="zName" placeholder="nom du bloc"></span>
    <span class="sep"></span>
    <span class="field"><label>Couleur</label><input type="color" id="zColor" value="#4da3ff"></span>
    <span class="sep"></span>
    <button id="addRect">+ Nouveau bloc</button>
    <button id="addLine">+ Ligne</button>
    <button id="delRect" class="danger">Supprimer</button>
    <span class="sep"></span>
    <button id="savePlan">💾 Enregistrer le plan pour ce jour</button>
    <button id="exportPng" class="secondary">Export PNG</button>
    <span class="sep"></span>
    <span class="field"><label>Répliquer ce plan du</label><input type="date" id="copyFrom"></span>
    <span class="field"><label>au</label><input type="date" id="copyUntil"></span>
    <button id="copyPlan" class="secondary">Répliquer</button>
  </div>
</div>

<div id="scene">
  <div id="svgWrap">
    <svg id="plan" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 495.75" preserveAspectRatio="xMidYMid meet"></svg>
  </div>
</div>

<script>window.PLAN_INIT_DATE = <?= json_encode($init_date) ?>;</script>
<script src="./plan_editor.js?v=10"></script>
</body>
</html>
