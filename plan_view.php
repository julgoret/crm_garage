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

  .legend {
    max-width: 900px; margin: 0 auto; padding: 4px 12px 16px;
    display: flex; flex-wrap: wrap; gap: 8px;
  }
  .legend-item {
    display: inline-flex; align-items: center; gap: 6px;
    background: #fff; border: 1px solid #dce1e8; border-radius: 14px;
    padding: 4px 12px; font-size: 12.5px;
  }
  .sw { width: 12px; height: 12px; border-radius: 3px; border: 1px solid rgba(0,0,0,.15); }
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
      <rect x="<?=$z['x']?>" y="<?=$z['y']?>" width="<?=$z['w']?>" height="<?=$z['h']?>"
            fill="<?=$z['color']?>" fill-opacity="0.45" stroke="<?=$z['color']?>" stroke-width="0.75" rx="4"/>
<?php endforeach; ?>
    </svg>
<?php endif; ?>
  </div>
</main>

<?php if(count($zones) > 0): ?>
<div class="legend">
<?php foreach($zones as $z): ?>
  <span class="legend-item"><span class="sw" style="background:<?=$z['color']?>"></span><?=htmlspecialchars($z['name'])?></span>
<?php endforeach; ?>
</div>
<?php endif; ?>

</body>
</html>
