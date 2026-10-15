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
    if (!isset($z['name']) || !is_string($z['name']) || mb_strlen($z['name']) > 200) return false;
    if (!isset($z['color']) || !preg_match('/^#[0-9a-fA-F]{6}$/', $z['color'])) return false;
    if (isset($z['type']) && $z['type'] === 'line') {
      foreach (array('x1','y1','x2','y2') as $k) {
        if (!isset($z[$k]) || !is_numeric($z[$k])) return false;
      }
    } else {
      foreach (array('x','y','w','h') as $k) {
        if (!isset($z[$k]) || !is_numeric($z[$k])) return false;
      }
    }
  }
  return true;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = isset($_GET['action']) ? $_GET['action'] : '';

if ($method === 'GET' && $action === 'get') {
  if (!valid_date($_GET['date'])) out(array('error' => 'date invalide'), 400);
  $date = $_GET['date'];
  $req = "SELECT zones FROM pl_plans WHERE date_plan = '" . $date . "' LIMIT 1";
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
  $req = "SELECT DATE_FORMAT(date_plan, '%Y-%m-%d') AS d FROM pl_plans";
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
    $req = "REPLACE INTO pl_plans (date_plan, zones, time_modif) VALUES ('" .
      $input['date'] . "', '" . addslashes($zones_json) . "', " . time() . ")";
    if (!mysqli_query($link, $req)) out(array('error' => 'erreur base'), 500);
    out(array('ok' => true, 'date' => $input['date'], 'count' => count($zones)));
  }

  if ($action === 'copy') {
    // les blocs viennent directement de la page courante
    if (!valid_zones($input['zones'])) out(array('error' => 'zones invalides'), 400);
    $zones_json = json_encode($input['zones']);

    // plage cible : du... au... (obligatoires)
    if (!valid_date($input['date'])) out(array('error' => "date de début de plage invalide"), 400);
    if (!isset($input['to']) || !valid_date($input['to'])) out(array('error' => "date de fin de plage invalide"), 400);
    if (strtotime($input['to']) < strtotime($input['date'])) out(array('error' => "date de fin antérieure à la date de début"), 400);

    $cur = strtotime($input['date']);
    $end = strtotime($input['to']);
    $done = 0;
    while ($cur <= $end) {
      $d = date('Y-m-d', $cur);
      $req = "REPLACE INTO pl_plans (date_plan, zones, time_modif) VALUES ('" .
        $d . "', '" . addslashes($zones_json) . "', " . time() . ")";
      if (!mysqli_query($link, $req)) out(array('error' => 'erreur base'), 500);
      $done++;
      $cur = strtotime('+1 day', $cur);
    }
    if ($done > 366) out(array('error' => "plage trop grande (max 366 jours)"), 400);
    out(array('ok' => true, 'date' => $input['date'], 'to' => $input['to'], 'count' => $done));
  }
}

out(array('error' => 'action inconnue'), 400);
?>
