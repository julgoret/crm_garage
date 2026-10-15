<?php
// Poussée manuelle d'un événement vers Google Agenda (bouton 🗓 de la fiche)
ini_set('display_errors', 0);
include_once('./connect_inc.php');
include_once('./gcal_lib.php');

// retour d'où on vient (fiche de l'événement)
$back = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : './index_light.php';

if(!is_numeric($_GET['id']))
{
  header('Location: '.$back);
  exit;
}

$req = "SELECT * FROM `prospects` WHERE `id`='".$_GET['id']."' LIMIT 1";
$res = mysqli_query($link, $req);
$row = mysqli_fetch_assoc($res);

if($row)
{
  gcal_push_event($link, $row);
}

header('Location: '.$back);
exit;
?>
