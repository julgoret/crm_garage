<?php

/*a2461*/
/*

enctype='multipart/form-data


if($row_data_event['time_beg']>0)
  {
    $val_time_beg = "value='".date("Y-m-d", $row_data_event['time_beg'])."'";
  }
  else
  {
    $val_time_beg = "";
  }
  if($row_data_event['time_end']>0)
  {
    $val_time_end = "value='".date("Y-m-d", $row_data_event['time_end'])."'";
  }
  else
  {
    $val_time_end = "";
  }
  $str_pop .= "<tr><td>Date début*</td><td> <input type='date' name='time_beg' ".$val_time_beg."></td></tr>";
  $str_pop .= "<tr><td>Date fin*</td><td> <input type='date' name='time_end' ".$val_time_end."></td></tr>";


  Image</span></td><td><img src='../../img/".$row_data_event['img_public']."' width='50px'><input type='file' name='fileToUpload' />

*/
/*a2461*/

include_once('./connect_inc.php');


// passage en session de la dernière requète pour ne pas perdre la recherche
if(isset($_GET['search_types']) && isset($_GET['search_etats']) && isset($_GET['archives'])){
  $_SESSION['last_search'] = "?search_types=".$_GET['search_types']."&search_etats=".$_GET['search_etats']."&archives=".$_GET['archives'];
}
else {
  $_SESSION['last_search'] = "";
}

// requete rappels du jour

$now = time();


// requètes sélecteurs de tri

  // type
  $select_type_search ="";

  $str_types_search = "<select name='search_types'><option value='0'> -- </option>";
  $req_ty="SELECT * FROM types";
  $res_ty  = mysqli_query($link, $req_ty);
  while($row_ty  = mysqli_fetch_assoc($res_ty))
  {

    if((isset($_GET['search_types']))&&($row_ty['id'] == $_GET['search_types']))
    {
      $select_type_search = "selected";
    }
    else {
      $select_type_search = "";
    }

    $str_types_search .= "<option value='".$row_ty['id']."' ".$select_type_search.">".$row_ty['name']."</option>";
  }

  $str_types_search .= "</select>";


  // etat


  $select_etat_search ="";
  $str_etat_search = "<select name='search_etats'><option value='0'> -- </option>";
  $req_et="SELECT * FROM etats ORDER BY ordo ASC";
  $res_et  = mysqli_query($link, $req_et);
  while($row_et  = mysqli_fetch_assoc($res_et))
  {

    if((isset($_GET['search_etats']))&&($row_et['id'] == $_GET['search_etats']))
    {
      $select_etat_search = "selected";
    }
    else {
      $select_etat_search = "";
    }

    $str_etat_search .= "<option value='".$row_et['id']."' ".$select_etat_search.">".$row_et['name']."</option>";
  }
  $str_etat_search .= "</select>";

// requete principale
$endreq ="";
$selecta0 = "";
$selecta1 = "";
$selecta2 = "";
if(!isset($_GET['archives']))
{
  $selecta0 = "checked";
  $endreq .= " AND archive ='0'";
}
if((isset($_GET['search_etats']))OR(isset($_GET['search_types']))OR(isset($_GET['archives'])))
{
  if($_GET['search_types']>0){

  $endreq .= " AND fkid_type ='".$_GET['search_types']."'";
  }
  if($_GET['search_etats']>0){

  $endreq .= " AND fkid_etat ='".$_GET['search_etats']."'";
  }
  if($_GET['archives']<2){

  $endreq .= " AND archive ='".$_GET['archives']."'";
  }


  if($_GET['archives']=='0'){
    $selecta0 = "checked";
  }
  if($_GET['archives']=='1'){
    $selecta1 = "checked";
  }
  if($_GET['archives']=='2'){
    $selecta2 = "checked";
  }


}





$req_m="SELECT * FROM prospects WHERE id>0 ".$endreq." ORDER BY id DESC";

$res_m  = mysqli_query($link, $req_m);
if(mysqli_num_rows($res_m)==0){//header('location:index.php');
}
//echo $req_m;
while($row_m  = mysqli_fetch_assoc($res_m))
{
  // on extrait l'état
  /*$str_etat = "";
  $color = "";
  $req_et="SELECT * FROM etats";
  $res_et  = mysqli_query($link, $req_et);
  while($row_et  = mysqli_fetch_assoc($res_et))
  {
    if($row_et['id'] == $row_m['fkid_etat']) {
      $select_etat = "selected";
      $color = $row_et['color'];
    }
    else {
      $select_etat = "";
    }


    $str_etat .= "<option value='".$row_et['id']."' ".$select_etat.">".$row_et['name']."</option>";
  }*/
/*  $str_etat .= "</select>";
  $str_etat_def = "<select name='etat' style='background-color:#".$color."'>".$str_etat;
*/
  // on extrait le type
  $search_types ="";
  $str_types = "<select name='types'>";

  $req_ty="SELECT * FROM types";
  $res_ty  = mysqli_query($link, $req_ty);
  while($row_ty  = mysqli_fetch_assoc($res_ty))
  {
    if($row_ty['id'] == $row_m['fkid_type']) {
      $select_type = "selected";
    }
    else {
      $select_type = "";
    }
    $str_types .= "<option value='".$row_ty['id']."' $select_type>".$row_ty['name']."</option>";


  }
  $str_types .= "</select>";

// etatts

  $str_main .= "<form action ='valid_event_light.php'  enctype='multipart/form-data'  method='post'>";
  $str_main .= "

  <tr id='i".$row_m['id']."'>
    <td>".$row_m['id']."</td>
    <td>".date('d/m/Y', $row_m['time_beg'] )."<br/>".$row_m['title_event']."</td>
    <td>
    <h3>Données publiques</h3>
    <table border='1'>
    <tr><td>Titre public : </td><td><input type='text' name='title_event' value=\"".$row_m['title_event']."\"><input type='hidden' name='id' value='".$row_m['id']."'></td></tr>
    <tr><td>Sous titre public : </td><td><input type='text' name='subtitle_public' value=\"".$row_m['subtitle_public']."\"></td></tr>
    <tr><td>Lien événement : </td><td><input type='text' name='link_event' value='".$row_m['link_event']."'></td></tr>
    <tr><td>Desc PUBLIC</td><td><textarea name ='desc_gene_public'>".$row_m['desc_gene_public']."</textarea></td></tr>
    ";

    if($row_m['time_beg']>0)
      {
        $val_time_beg = "value='".date("Y-m-d", $row_m['time_beg'])."'";
      }
      else
      {
        $val_time_beg = "";
      }
      if($row_m['time_end']>0)
      {
        $val_time_end = "value='".date("Y-m-d", $row_m['time_end'])."'";
      }
      else
      {
        $val_time_end = "";
      }
      $str_main .= "<tr><td>Date début* </td><td><input type='date' name='time_beg' ".$val_time_beg."></td></tr>";
      $str_main .= "<tr><td>Date fin* </td><td><input type='date' name='time_end' ".$val_time_end."></td></tr>";


      $str_main .= "<tr><td>Image (1024px/648px): </td><td><img src='../../img/".$row_m['img_public']."' width='50px'><input type='file' name='fileToUpload' /></td></tr></table>";
    $str_main .= "</td><td>

    <table>
    <h3>Contact événement</h3>
      <tr><td>NOM CONTACT</td><td><input type='text' name='name' value='".$row_m['name']."'></td></tr>
    <tr><td>MAIL</td><td><input type='text' name='mail' value='".$row_m['mail']."'></td></tr>
    <tr><td>TEL</td><td><input type='text' name='tel' value='".$row_m['tel']."'></td></tr>
    </table>

    </td>
    <td><input type='hidden' name='id' value='".$row_m['id']."'><input type='submit' value ='VALIDER'>";

    if($row_m['archive'] == 0)
    {
      $str_main_ar_txt = "ARCHIVER";
      $val_archive = 1;
    }
    else
    {
      $str_main_ar_txt = "D&Eacute;SARCHIVER";
      $val_archive = 0;
    }

    $str_main .= "<br/><a href='./del_line.php?from=simple&id=".$row_m['id']."&val=".$val_archive."'>";

    $str_main .= $str_main_ar_txt."</a><br/><a href='./del_def.php?from=simple&id=".$row_m['id']."'>SUPPRIMER</a></td>
  </tr>";
  $str_main .= "</form>";
}

// popup suivi event


?>
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
  <head>
    <meta charset="utf-8">
    <title>CRM Suivi flux entrants</title>
    <style>
    *{font-family: Arial;}
    .pop{
      position: absolute;
      top:15px;

      left: 50%; /* à 50%/50% du parent référent */
      transform: translate(-50%, 0%); /* décalage de 50% de sa propre taille */
      width:700px;
      min-height:50%;
      background-color: #ffffff;
      border: thick double #32a1ce;
      text-align: center;
      }
      .pop table{

        margin:15px;
      }
      .red
      {
        background-color: #ff7b7b;
      }
      .green
      {
        background-color: #a6ff7b;
      }
      .yellow
      {
        background-color: #ffee7b;
      }
      .grey
      {
        background-color: #cacaca;
      }

      .tabcol{
        width:600px;
      }
      .tabcol_list{
        width:300px;
      }
      .pop textarea{
        width:300px;

      }
      .pop input{
        width:300px;
      }
      .pop select{
        width:300px;
      }
      .pop td{
        padding: 5px;
      }
    </style>
  </head>
  <body>
<?php
echo "<h1>&Eacute;V&Eacute;NEMENTS</h1>";
echo "<h2>LISTE PRINCIPALE</h2>";
echo "<h3>INSERER UN &Eacute;V&Eacute;NEMENT<h3>"
?>
<form action='tr_insert.php' method='post'>
  <table border="1">
    <tr>
<input type='hidden' name='from' value='simple'>
      <td><input type='text' name='name' placeholder='NOM CONTACT'></td>
      <td><input type='text' name='mail' placeholder='MAIL'></td>
      <td><input type='text' name='tel' placeholder='TEL'></td>
      <td><input type='submit' value ='Insérer'></td>
    </tr>
  </table>
</form>
<h3>TRIER<h3>
<form action='' method="get">
  <table border="1">
    <tr>


      <td>ACTIFS <input type="radio" name="archives" value="0" <?=$selecta0?>><br/>ARCHIVES <input type="radio" name="archives" value="1" <?=$selecta1?>><br/>TOUS <input type="radio" name="archives" value="2" <?=$selecta2?>></td>
      <td><input type='submit' value ='TRIER'></td>
    </tr>
  </table>
</form>
<?php
echo "<table border='1'><tr>
  <td>ID</td>
  <td>TITRE</td>

  <td><h3>Données publiques</h3></td>

  <td><h3>Données contact</h3></td>
  <td></td>
</tr>".$str_main."</table>";

?>

</body>
</html>
