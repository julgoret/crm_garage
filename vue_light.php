<?php
ini_set('display_errors', 0);
include_once('./connect_inc.php');

// événements non archivés
$str_events = "";
$req = "SELECT * FROM `prospects` WHERE `archive`='0' ORDER BY `time_beg` ASC";
$res = mysqli_query($link, $req);
while($row = mysqli_fetch_assoc($res))
{
  // date affichée : début (ou "non daté")
  if($row['time_beg'] > 1)
  {
    $str_date = date("d/m/Y", $row['time_beg']);
    if(($row['time_end'] > 1)&&($row['time_end'] != $row['time_beg']))
    {
      $str_date .= " → ".date("d/m/Y", $row['time_end']);
    }
  }
  else
  {
    $str_date = "non daté";
  }

  $str_events .= "
  <div class='ev-block' id='ev".$row['id']."'>
    <div class='ev-head'>
      <div class='ev-title'>".stripslashes($row['title_event'])."</div>
      <div class='ev-meta'><span class='ev-date'>".$str_date."</span><span class='ev-horaires'>".nl2br(stripslashes($row['horaires']))."</span></div>
      ";
  $str_events .= "
    </div>
    <div class='ev-tabs'>";
  if($row['time_beg'] > 1)
  {
    $str_events .= "<a class='ev-plan-link' href='plan_view.php?date=".date('Y-m-d', $row['time_beg'])."' target='_blank'>Plan</a>";
  }

  // ----- onglet DONNÉES ÉVÉNEMENT -----
  $str_events .= "
      <details class='ev-tab'>
        <summary>Données événement</summary>
        <div class='tab-panel'>        <table>
          <tr><td class='tabcol_list'>Type</td><td>";
  $req_type = "SELECT * FROM `types`";
  $res_type = mysqli_query($link, $req_type);
  while($row_type = mysqli_fetch_assoc($res_type))
  {
    if($row_type['id'] == $row['fkid_type'])
    {
      $str_events .= $row_type['name'];
    }
  }
  $str_events .= "</td></tr>
          <tr><td class='tabcol_list'>Date début accueil public</td><td>".(($row['time_beg'] > 1) ? date("d/m/Y", $row['time_beg']) : "")."</td></tr>
          <tr><td class='tabcol_list'>Date fin accueil public</td><td>".(($row['time_end'] > 1) ? date("d/m/Y", $row['time_end']) : "")."</td></tr>
          <tr><td class='tabcol_list'>Horaires</td><td>".nl2br(stripslashes($row['horaires']))."</td></tr>
          <tr><td class='tabcol_list'>Organisateur (Structure + référent)</td><td>".nl2br(stripslashes($row['name']))."</td></tr>
          <tr><td class='tabcol_list'>Tel</td><td>".nl2br($row['tel'])."</td></tr>
          <tr><td class='tabcol_list'>Mail</td><td>".nl2br($row['mail'])."</td></tr>
          <tr><td class='tabcol_list'>Lien dossier</td><td>".nl2br($row['link_devis'])."</td></tr>
          <tr><td class='tabcol_list'>Déroulé</td><td>".nl2br(stripslashes($row['comment_deroule']))."</td></tr>
        </table></div>
      </details>";

  // ----- onglet MAIN D'OEUVRE -----
  $str_ref = ""; $str_ref_dday = "";
  $req_op = "SELECT * FROM `operateurs`";
  $res_op = mysqli_query($link, $req_op);
  while($row_op = mysqli_fetch_assoc($res_op))
  {
    if($row_op['id'] == $row['fkid_operateur']) { $str_ref = $row_op['name']; }
    if($row_op['id'] == $row['fkid_operateur_dday']) { $str_ref_dday = $row_op['name']; }
  }
  $str_events .= "
      <details class='ev-tab'>
        <summary>Main d'oeuvre</summary>
        <div class='tab-panel'>        <table>
          <tr><td class='tabcol_list'>Référent général</td><td>".$str_ref."</td></tr>
          <tr><td class='tabcol_list'>Référent Jour J</td><td>".$str_ref_dday."</td></tr>
          <tr><td class='tabcol_list'>Main d'oeuvre</td><td>".nl2br(stripslashes($row['mo_comm']))."</td></tr>
        </table></div>
      </details>";

  // ----- onglet RÉGIE / ESPACES -----
  $str_events .= "
      <details class='ev-tab'>
        <summary>Régie / Espaces</summary>
        <div class='tab-panel'>        <table>
          <tr><td class='tabcol_list'>Jauge estimée</td><td>".nl2br(stripslashes($row['jauge_ttl']))."</td></tr>
          <tr><td class='tabcol_list'>Régie</td><td>".nl2br(stripslashes($row['espaces']))."</td></tr>
        </table></div>
      </details>";

  // ----- onglet RESTAURATION -----
  $str_events .= "
      <details class='ev-tab'>
        <summary>Restauration</summary>
        <div class='tab-panel'>        <table>
          <tr><td class='tabcol_list'>Besoin resto public</td><td>".nl2br(stripslashes($row['comment_resto_public']))."</td></tr>
          <tr><td class='tabcol_list'>Besoin resto catering</td><td>".nl2br(stripslashes($row['comment_resto_cater']))."</td></tr>
          <tr><td class='tabcol_list'>Courses spécifiques</td><td>".nl2br(stripslashes($row['liste_courses']))."</td></tr>
          <tr><td class='tabcol_list'>Moyens paiement</td><td>".nl2br(stripslashes($row['moyens_pay']))."</td></tr>
        </table></div>
      </details>";

  // ----- onglet COMMUNICATION -----
  $str_events .= "
      <details class='ev-tab'>
        <summary>Communication</summary>
        <div class='tab-panel'>        <table>
          <tr><td class='tabcol_list'>Remarques / Contact Com</td><td>".nl2br(stripslashes($row['com_comm']))."</td></tr>
          <tr><td class='tabcol_list'>Comptes RS à mentionner</td><td>".nl2br(stripslashes($row['comptes_rs']))."</td></tr>
          <tr><td class='tabcol_list'>Lien ressources Com</td><td>".nl2br($row['com_lien'])."</td></tr>
        </table></div>
      </details>";

  $str_events .= "
    </div>
  </div>";
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Consultation événements — Garage Moderne</title>
<link rel="stylesheet" href="./admin.css">
<style>
  .ev-list { width: 100%; max-width: 900px; }
  body { padding: 0 10px 30px; }
  h1 { font-size: 17px; }
  .ev-title { font-size: 15px; }
  .ev-meta { flex-direction: column; gap: 2px; font-size: 13px; }
  .ev-block { border-radius: 10px; }
  .ev-tabs { padding: 0 12px 12px; }
  .ev-head { padding: 10px 12px 6px; }
  .ev-tab td { font-size: 12.5px; padding: 5px 8px; }
  .ev-tab .tabcol_list { width: 42%; }
  @media (min-width: 700px) {
    body { padding: 0 24px 48px; }
    h1 { font-size: 20px; }
    .ev-title { font-size: 16px; }
    .ev-meta { flex-direction: row; gap: 18px; font-size: 13px; }
    .ev-head { padding: 12px 16px 8px; }
    .ev-tabs { padding: 0 16px 12px; }
    .ev-tab td { font-size: 13px; padding: 5px 10px; }
  }
  .ev-block {
    background: var(--surface);
    border: 1px solid var(--border);
    border-left: 4px solid var(--accent);
    border-radius: var(--radius);
    box-shadow: var(--shadow);
    margin-bottom: 14px;
  }
  .ev-head { padding: 12px 16px 8px; }
  .ev-title { font-size: 16px; font-weight: 600; }
  .ev-meta { display: flex; gap: 18px; margin-top: 3px; font-size: 13px; color: var(--text-muted); }
  .ev-date { font-weight: 600; color: var(--accent-dark); white-space: nowrap; }
  .ev-horaires { white-space: nowrap; }
  .ev-plan-link {
    display: inline-block; background: var(--accent); color: #fff;
    border-radius: 20px; padding: 5px 14px;
    font-size: 12.5px; font-weight: 600; text-decoration: none;
    white-space: nowrap;
  }
  .ev-plan-link:hover { background: var(--accent-dark); text-decoration: none; }
  .ev-plan-link:visited, .ev-plan-link:visited:hover { color: #fff; }
  .ev-tabs { display: flex; flex-wrap: wrap; gap: 8px; padding: 0 16px 12px; }
  .ev-tab { position: relative; }
  .ev-tab summary {
    cursor: pointer; list-style: none; user-select: none;
    background: #eef2f7; border: 1px solid var(--border); border-radius: 20px;
    padding: 5px 14px; font-size: 12.5px; font-weight: 600; color: var(--accent-dark);
    white-space: nowrap;
  }
  .ev-tab summary:hover { background: #e2ecf5; }
  .ev-tab summary::-webkit-details-marker { display: none; }
  .ev-tab summary::after { content: " ▾"; font-size: 10px; color: var(--text-muted); }
  .ev-tab[open] summary::after { content: " ▴"; }
  /* mobile-first : le panneau s'insère dans le flux sous les onglets */
  .ev-tab .tab-panel {
    background: var(--surface); border: 1px solid var(--border);
    border-radius: var(--radius); box-shadow: 0 2px 10px rgba(31,39,51,.12);
    width: 100%; padding: 6px; margin-top: 6px;
  }
  .ev-tab[open] { width: 100%; }
  /* en desktop (large), le panneau redevient un popover flottant */
  @media (min-width: 700px) {
    .ev-tab .tab-panel {
      position: absolute; top: calc(100% + 6px); left: 0; z-index: 20;
      min-width: 420px; max-width: 560px; width: auto;
      box-shadow: 0 8px 26px rgba(31,39,51,.18);
    }
    .ev-tab[open] { width: auto; }
  }
  .ev-tab table { margin: 0; box-shadow: none; border: none; width: 100%; }
  .ev-tab td { font-size: 13px; padding: 5px 10px; }
  .ev-tab tr:first-child td {
    background: none; text-transform: none; letter-spacing: normal;
    font-weight: 400; font-size: 13px; color: var(--text);
  }
  .ev-tab .tabcol_list { width: 45%; background: #f7f9fc; color: var(--text-muted); }
</style>
</head>
<body>
<h1>Consultation événements</h1>
<div class="ev-list">
<?=$str_events?>
</div>
</body>
</html>
