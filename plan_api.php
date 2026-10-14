<?php
ini_set('display_errors', 0);
include_once('./connect_inc.php');

header('Content-Type: application/json; charset=utf-8');

function out($data, $code = 200) {
  http_response_code($code);
  echo json_encode($data);
  exit;
}

// date valide YYYY-MM-DD ?
function valid_date($d) {
  return is_string($d) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) && strtotime($d) !== false;
}

// données de zones : tableau d'objets {name, x, y, w, h, color}
function valid_zones($zones) {
  if (!is_array($zones)) return false;
  foreach ($zones as $z) {
    if (!is_array($z)) return false;
    foreach (array('name','x','y','w','h','color') as $k) {
      if (!array_key_exists($k, $z)) return false;
    }
    if (!is_numeric($z['x']) || !is_numeric($z['y']) || !is_numeric($z['w']) || !is_numeric($z['h'])) return false;
    if (!is_string($z['name']) || mb_strlen($z['name']) > 200) return false;
    if (!preg_match('/^#[0-9a-fA-F]{6}$/', $z['color'])) return false;
  }
  return true;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($method === 'GET' && $action === 'get') {
  if (!valid_date($_GET['date'])) out(array('error' => 'date invalide'), 400);
  $date = $_GET['date'];
  $req = "SELECT zones FROM plans WHERE date_plan = '" . $date . "' LIMIT 1";
  $res = mysqli_query($link, $req);
  if (!$res) out(array('error' => 'erreur base'), 500);
  $row = mysqli_fetch_assoc($res);
  out(array(
    'date' => $date,
    'zones' => $row ? json_decode($row['zones'], true) : array()
  ));
}

if ($method === 'GET' && $action === 'range') {
  // liste des dates ayant un plan sauvegardé (pour marquer le curseur)
  $req = "SELECT DATE_FORMAT(date_plan, '%Y-%m-%d') AS d FROM plans";
  $res = mysqli_query($link, $req);
  if (!$res) out(array('error' => 'erreur base'), 500);
  $dates = array();
  while ($row = mysqli_fetch_assoc($res)) { $dates[] = $row['d']; }
  out(array('dates' => $dates));
}

if ($method === 'POST' && ($action === 'save' || $action === 'copy')) {
  $input = json_decode(file_get_contents('php://input'), true);
  if (!valid_date($input['date'])) out(array('error' => 'date invalide'), 400);

  if ($action === 'save') {
    $zones = $input['zones'];
    if (!valid_zones($zones)) out(array('error' => 'zones invalides'), 400);
    $zones_json = json_encode($zones);
    $req = "REPLACE INTO plans (date_plan, zones, time_modif) VALUES ('" .
      $input['date'] . "', '" . addslashes($zones_json) . "', " . time() . ")";
    if (!mysqli_query($link, $req)) out(array('error' => 'erreur base'), 500);
    out(array('ok' => true, 'date' => $input['date'], 'count' => count($zones)));
  }

  if ($action === 'copy') {
    if (!valid_date($input['from'])) out(array('error' => "date source invalide"), 400);
    $req = "SELECT zones FROM plans WHERE date_plan = '" . $input['from'] . "' LIMIT 1";
    $res = mysqli_query($link, $req);
    $row = mysqli_fetch_assoc($res);
    if (!$row) out(array('error' => 'aucun plan sauvegardé pour cette date'), 404);
    $req = "REPLACE INTO plans (date_plan, zones, time_modif) VALUES ('" .
      $input['date'] . "', '" . addslashes($row['zones']) . "', " . time() . ")";
    if (!mysqli_query($link, $req)) out(array('error' => 'erreur base'), 500);
    out(array('ok' => true, 'date' => $input['date']));
  }
}

out(array('error' => 'action inconnue'), 400);
?>
