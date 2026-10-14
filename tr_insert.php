<?php
include_once('./connect_inc.php');

$req_ins = 'INSERT INTO `prospects` ( `title_event`, `time_crea` ) VALUES (  "'.addslashes($_POST["name"]).'", "'.time().'")';
//echo "xx".$req_ins;
mysqli_query($link, $req_ins);
// id inséré

$req_chk = "SELECT `id` FROM `prospects` ORDER BY `id` DESC";
$res_chk = mysqli_query($link, $req_chk);
$row_chk = mysqli_fetch_assoc($res_chk);
///echo $row_chk['id'];///echo $row_chk['id'];
if($_POST['from']=="simple"){
  header("Location: ./index0.php?id_ev=".$row_chk['id']);
  //echo $_POST['from']."XX";
}else {
  header("Location: ./index.php?id_ev=".$row_chk['id']);
}
?>
