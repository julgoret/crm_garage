<?php
ini_set('display_errors', 0);
include_once('./connect_inc.php');
header('Content-Type: text/html; charset=utf-8');

// jour demandé : ?date=YYYY-MM-DD (sinon aujourd'hui)
$date = date('Y-m-d');
if(isset($_GET['date']) && preg_match('/^\d{4}-\d{2}-\d{2}$/', $_GET['date']) && strtotime($_GET['date']) !== false)
{
  $date = $_GET['date'];
}

// blocs du jour
$zones = array();
$req = "SELECT zones FROM pl_plans WHERE date_plan = '".$date."' LIMIT 1";
$res = mysqli_query($link, $req);
if($res)
{
  $row = mysqli_fetch_assoc($res);
  if($row)
  {
    $decoded = json_decode($row['zones'], true);
    if(is_array($decoded)) { $zones = $decoded; }
  }
}

// libellé du jour en toutes lettres
$ts = strtotime($date);
$days = array("dimanche","lundi","mardi","mercredi","jeudi","vendredi","samedi");
$months = array("janvier","février","mars","avril","mai","juin","juillet","août","septembre","octobre","novembre","décembre");
$label = $days[date("w", $ts)]." ".date("j", $ts)." ".$months[date("n", $ts) - 1]." ".date("Y", $ts);

// découpe du titre d'un bloc en lignes tenant dans sa largeur (unités viewBox)
// estimation : ~0.62 * taille de police de largeur moyenne par caractère
function wrap_lines($str, $max_width, $font_size = 13)
{
  $max_chars = max(4, (int)floor($max_width / ($font_size * 0.62)));
  $words = preg_split('/\s+/', trim($str));
  $lines = array();
  $line = "";
  foreach($words as $w)
  {
    $test = ($line === "") ? $w : $line." ".$w;
    if(mb_strlen($test) > $max_chars && $line !== "")
    {
      $lines[] = $line;
      $line = $w;
    }
    else
    {
      $line = $test;
    }
  }
  if($line !== "") { $lines[] = $line; }
  if(empty($lines)) { $lines = array(""); }
  return $lines;
}

// date précédente/suivante pour la navigation
$prev = date('Y-m-d', $ts - 86400);
$next = date('Y-m-d', $ts + 86400);
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
<title>Plan du <?=$label?></title>
<style>
  * { box-sizing: border-box; font-family: "Segoe UI", system-ui, -apple-system, Arial, sans-serif; }
  html, body { margin: 0; padding: 0; background: #f2f4f8; color: #1f2733; }
  body { display: flex; flex-direction: column; min-height: 100vh; font-size: 14px; }

  header {
    position: sticky; top: 0; z-index: 10;
    background: #fff; border-bottom: 1px solid #dce1e8;
    box-shadow: 0 1px 3px rgba(31,39,51,.08);
    padding: 10px 12px;
  }
  .head-row { display: flex; align-items: center; gap: 10px; max-width: 900px; margin: 0 auto; }
  .day-label { flex: 1; font-size: 16px; font-weight: 600; line-height: 1.3; }
  .nav-btn {
    display: inline-block; background: #0f6db3; color: #fff; text-decoration: none;
    border-radius: 8px; padding: 9px 14px; font-size: 15px; font-weight: 600;
    -webkit-tap-highlight-color: transparent; touch-action: manipulation;
  }
  .nav-btn:active { background: #0b5288; }

  main { flex: 1; display: flex; align-items: center; justify-content: center; padding: 10px; }
  #scene { width: 100%; max-width: 900px; }
  svg { width: 100%; height: auto; display: block; border-radius: 8px;
        box-shadow: 0 2px 10px rgba(31,39,51,.12); background: #fff; }

  .empty {
    text-align: center; color: #6b7686; padding: 40px 20px; font-size: 14px;
  }
</style>
</head>
<body>

<header>
  <div class="head-row">
    <a class="nav-btn" href="plan_view.php?date=<?=$prev?>" title="Jour précédent">◀</a>
    <div class="day-label"><?=$label?></div>
    <a class="nav-btn" href="plan_view.php?date=<?=$next?>" title="Jour suivant">▶</a>
  </div>
</header>

<main>
  <div id="scene">
<?php if(count($zones) == 0): ?>
    <div class="empty">Aucun bloc enregistré pour ce jour.</div>
<?php else: ?>
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1000 495.75" preserveAspectRatio="xMidYMid meet">
      <image href="./fond_plan.png" x="0" y="0" width="1000" height="495.75" preserveAspectRatio="xMidYMid meet"/>
<?php foreach($zones as $z): ?>
<?php if(isset($z['type']) && $z['type'] === 'line'): ?>
      <line x1="<?=$z['x1']?>" y1="<?=$z['y1']?>" x2="<?=$z['x2']?>" y2="<?=$z['y2']?>"
            stroke="<?=$z['color']?>" stroke-width="<?=isset($z['thick']) ? $z['thick'] : 3?>" stroke-linecap="round"/>
      <text x="<?=($z['x1'] + $z['x2']) / 2?>" y="<?=($z['y1'] + $z['y2']) / 2 - 8?>"
            text-anchor="middle" font-size="13" font-weight="400" fill="#1f2733"
            pointer-events="none"><?=htmlspecialchars($z['name'])?></text>
<?php else: ?>
      <rect x="<?=$z['x']?>" y="<?=$z['y']?>" width="<?=$z['w']?>" height="<?=$z['h']?>"
            fill="<?=$z['color']?>" fill-opacity="0.45" stroke="<?=$z['color']?>" stroke-width="0.75" rx="4"/>
      <text text-anchor="middle" font-size="13" font-weight="400" fill="#1f2733" pointer-events="none">
<?php
      $lines = wrap_lines($z['name'], $z['w'] - 12);
      $base_y = $z['y'] + $z['h'] / 2 + 4.5 - (count($lines) - 1) * 8;
      foreach($lines as $k => $ln):
?>
        <tspan x="<?=$z['x'] + $z['w'] / 2?>" y="<?=$base_y + $k * 16?>"><?=htmlspecialchars($ln)?></tspan>
<?php endforeach; ?>
      </text>
<?php endif; ?>
<?php endforeach; ?>
    </svg>
<?php endif; ?>
  </div>
</main>

</body>
</html>
