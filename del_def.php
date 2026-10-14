<?php
include_once('./connect_inc.php');


$req_ins = 'DELETE FROM `prospects` WHERE id = '.$_GET['id'].' LIMIT 1' ;
mysqli_query($link, $req_ins);
//echo $req_ins;
if($_GET['from']=="simple"){
  header("Location: ./index0.php".$_SESSION['last_search']);
  //echo $_POST['from']."XX";
}else {
  header("Location: ./index.php".$_SESSION['last_search']);
}
?>
