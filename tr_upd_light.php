<?php    include_once('./connect_inc.php');


// maketime
//echo $_POST['time_rdv'];
$time_rdv = strtotime($_POST['time_rdv']);
if(empty($time_rdv)){$time_rdv = 0;}
/*$time_rdv = explode("/", $_POST['time_rdv']);
$time_rdv_def = $time_rdv[1]."/".$time_rdv[0]."/".$time_rdv[2];
$time_rdv = strtotime($time_rdv_def);*/
//echo $_POST['time_rdv']."// ".$time_rdv_def;
//
if(!empty($_POST['comment']))
{$dateinsert = date("d/m/Y H:i");}
else {
  $dateinsert = "";
}
$req_ins = 'UPDATE `prospects` SET `mail`="'.addslashes($_POST['mail']).'",`name`="'.addslashes($_POST['name']).'",`tel`="'.addslashes($_POST['tel']).'",`fkid_etat`="'.addslashes($_POST['etat']).'",`fkid_type`="'.addslashes($_POST['types']).'",`comment`="'.$dateinsert." ".addslashes($_POST['comment']).'",`time_last_action`="'.time().'",`time_rappel`="'.$time_rdv.'" WHERE `id` = "'.$_POST['id'].'" LIMIT 1' ;
//echo $req_ins;
mysqli_query($link, $req_ins);
header("Location: ./index0.php".$_SESSION['last_search'])
?>
