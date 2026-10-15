<?php
ini_set('display_errors', 0);
include_once('./connect_inc.php');

/*
$time_beg = explode(" ", $_POST['time_beg']);
$time_beg_date = $time_beg[0];
$time_beg_date_def = explode("/", $time_beg_date);
$time_beg_heure = $time_beg[1];
$time_beg_date_en = $time_beg_date_def[1]."/".$time_beg_date_def[0]."/".$time_beg_date_def[2];
$date_beg_norm = $time_beg_date_en." ".$time_beg_heure;
//$time_rdv = strtotime($time_rdv_def);
$time_beg = strtotime($date_beg_norm);


$time_end = explode(" ", $_POST['time_end']);
$time_end_date = $time_end[0];
$time_end_date_def = explode("/", $time_end_date);
$time_end_heure = $time_end[1];
$time_end_date_en = $time_end_date_def[1]."/".$time_end_date_def[0]."/".$time_end_date_def[2];
$date_end_norm = $time_end_date_en." ".$time_end_heure;
//$time_rdv = strtotime($time_rdv_def);
$time_end = strtotime($date_end_norm);
*/
$time_beg = strtotime($_POST['time_beg']);
$time_end = strtotime($_POST['time_end']);

//fileToUpload
//fileToUpload
if(!empty($_FILES["fileToUpload"]["name"]))
{
  include_once("./upl.php");
  $req_media = "`img_public`='".$newname."' ,";

  //echo $target_file;
  chmod($target_file, 777);
}
if(!empty($_FILES["fileToUpload_2"]["name"]))
{
  include_once("./upl_logo.php");
  $req_media .= "`img_logo`='".$newname."' ,";

  //echo $target_file;
  chmod($target_file, 777);
}

$req_ins = 'UPDATE `prospects` SET
`name`="'.addslashes($_POST['name']).'",
`fkid_type`="'.addslashes($_POST['fkid_type']).'",
`tel`="'.addslashes($_POST['tel']).'",
`mail`="'.addslashes($_POST['mail']).'",
`desc_gene`="'.addslashes($_POST['desc_gene']).'",
`title_event`="'.addslashes($_POST['title_event']).'",
`link_event`="'.addslashes($_POST['link_event']).'",
`link_devis`="'.addslashes($_POST['link_devis']).'",
`link_bene`="'.addslashes($_POST['link_bene']).'",
`link_insc`="'.addslashes($_POST['link_insc']).'",
`time_beg`="'.addslashes($time_beg).'",
`time_end`="'.addslashes($time_end).'",
`jauge_ttl`="'.addslashes($_POST['jauge_ttl']).'",
`fkid_operateur`="'.addslashes($_POST['fkid_operateur']).'",
`fkid_operateur_dday`="'.addslashes($_POST['fkid_operateur_dday']).'",
`espaces`="'.addslashes($_POST['espaces']).'",
`todo-before`="'.addslashes($_POST['todo-before']).'",
`comment_deroule`="'.addslashes($_POST['comment_deroule']).'",
`todo-after`="'.addslashes($_POST['todo-after']).'",
`comment_resto_public`="'.addslashes($_POST['comment_resto_public']).'",
`comment_resto_cater`="'.addslashes($_POST['comment_resto_cater']).'",
`mo_comm`="'.addslashes($_POST['mo_comm']).'",
`liste_courses`="'.addslashes($_POST['liste_courses']).'",
`desc_gene`="'.addslashes($_POST['desc_gene']).'",
`subtitle_public`="'.addslashes($_POST['subtitle_public']).'",
'.$req_media.'
`desc_gene_public`="'.addslashes($_POST['desc_gene_public']).'",
`horaires`="'.addslashes($_POST['horaires']).'",
`horaire_technique`="'.addslashes($_POST['horaire_technique']).'",
`programme`="'.addslashes($_POST['programme']).'",
`col`="'.addslashes($_POST['col']).'",
`lien_plan` ="'.addslashes($_POST['lien_plan']).'",
`ferm_ateliers` ="'.addslashes($_POST['ferm_ateliers']).'",
`mobilier` ="'.addslashes($_POST['mobilier']).'",
`mat_tech` ="'.addslashes($_POST['mat_tech']).'",
`vehicules` ="'.addslashes($_POST['vehicules']).'",
`com_comm`="'.addslashes($_POST['com_comm']).'",
`com_lien`="'.addslashes($_POST['com_lien']).'",
`id_com_categ`="'.$_POST['categ'].'",
`moyens_pay`="'.$_POST['moyens_pay'].'",
`comptes_rs`="'.$_POST['comptes_rs'].'"
WHERE id = '.$_POST['id'].' LIMIT 1' ;
//echo "XX".$_POST['backid']; comptes_rs
//echo $req_ins;
//echo " // ".time();
//echo $_SERVER['HTTP_REFERER']."?id_ev=".$_POST['id'];
/*title_event
`com_etat`="'.addslashes($_POST['com_etat']).'",
`com_comm`="'.addslashes($_POST['com_comm']).'",
`com_lien`="'.addslashes($_POST['com_lien']).'",*/
mysqli_query($link, $req_ins);

// poussée automatique vers Google Agenda (silencieuse si désactivée)
include_once('./gcal_lib.php');
$req_gcal = "SELECT * FROM `prospects` WHERE `id`='".$_POST['id']."' LIMIT 1";
$res_gcal = mysqli_query($link, $req_gcal);
$row_gcal = mysqli_fetch_assoc($res_gcal);
if($row_gcal)
{
  gcal_push_event($link, $row_gcal);
}
header("Location: ".$_SERVER['HTTP_REFERER']."#".$_POST['backid'])
?>
