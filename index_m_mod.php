<?php
ini_set('display_errors', 0);
/*a2461*/

/*a2461*/

include_once('./connect_inc.php');
/*


*/
if(is_numeric($_POST['id']))
{
$time_beg = strtotime($_POST['time_beg']);
$time_end = strtotime($_POST['time_end']);

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
`tel`="'.addslashes($_POST['tel']).'",
`mail`="'.addslashes($_POST['mail']).'",
`desc_gene`="'.addslashes($_POST['desc_gene']).'",
`title_event`="'.addslashes($_POST['title_event']).'",
`link_event`="'.addslashes($_POST['link_event']).'",
`link_devis`="'.addslashes($_POST['link_devis']).'",
`fkid_etat`="'.addslashes($_POST['fkid_etat']).'",
`time_beg`="'.addslashes($time_beg).'",
`time_end`="'.addslashes($time_end).'",
`fkid_etat`="'.addslashes($_POST['fkid_etat']).'",
`jauge_ttl`="'.addslashes($_POST['jauge_ttl']).'",
`jauge_simul`="'.addslashes($_POST['jauge_simul']).'",
`fkid_operateur`="'.addslashes($_POST['fkid_operateur']).'",
`fkid_operateur_dday`="'.addslashes($_POST['fkid_operateur_dday']).'",
`espaces`="'.addslashes($_POST['espaces']).'",
`todo-before`="'.addslashes($_POST['todo-before']).'",
`comment_deroule`="'.addslashes($_POST['comment_deroule']).'",
`todo-after`="'.addslashes($_POST['todo-after']).'",
`comment_resto_public`="'.addslashes($_POST['comment_resto_public']).'",
`comment_resto_cater`="'.addslashes($_POST['comment_resto_cater']).'",
`link_devis`="'.addslashes($_POST['link_devis']).'",
`mo_bene_etat`="'.addslashes($_POST['mo_bene_etat']).'",
`mo_salaries_etat`="'.addslashes($_POST['mo_salaries_etat']).'",
`mo_comm`="'.addslashes($_POST['mo_comm']).'",
`liste_courses`="'.addslashes($_POST['liste_courses']).'",
`desc_gene`="'.addslashes($_POST['desc_gene']).'",
`subtitle_public`="'.addslashes($_POST['subtitle_public']).'",
'.$req_media.'
`desc_gene_public`="'.addslashes($_POST['desc_gene_public']).'",
`horaires`="'.addslashes($_POST['horaires']).'",
`programme`="'.addslashes($_POST['programme']).'",
`lien_plan` ="'.addslashes($_POST['lien_plan']).'",
`ferm_ateliers` ="'.addslashes($_POST['ferm_ateliers']).'",
`mobilier` ="'.addslashes($_POST['mobilier']).'",
`mat_tech` ="'.addslashes($_POST['mat_tech']).'",
`vehicules` ="'.addslashes($_POST['vehicules']).'",
`com_comm`="'.addslashes($_POST['com_comm']).'",
`com_lien`="'.addslashes($_POST['com_lien']).'"
WHERE id = '.$_POST['id'].' LIMIT 1' ;
//echo $req_ins;
//echo " // ".time();

/*title_event
`com_etat`="'.addslashes($_POST['com_etat']).'",
`com_comm`="'.addslashes($_POST['com_comm']).'",
`com_lien`="'.addslashes($_POST['com_lien']).'",*/
mysqli_query($link, $req_ins);}

// passage en session de la dernière requète pour ne pas perdre la recherche
if(isset($_GET['search_types']) && isset($_GET['search_etats']) && isset($_GET['archives'])){
  $_SESSION['last_search'] = "?search_types=".$_GET['search_types']."&search_etats=".$_GET['search_etats']."&archives=".$_GET['archives'];
}
else {
  $_SESSION['last_search'] = "";
}

// requete rappels du jour

$now = time();
$str_rappels ="";
$str_main ="";
$req="SELECT * FROM prospects WHERE time_rappel < ".$now." AND archive =0 AND time_rappel > 0";
$res  = mysqli_query($link, $req);
while($row  = mysqli_fetch_assoc($res))
{
  $str_rappels .= "<tr><td>".$row['id']."</td><td>".$row['name']."</td><td>".$row['mail']."</td><td>".$row['tel']."</td><td>".date('d/m/Y', $row['time_rappel'] )."</td><td><a href='index.php".$_SESSION['last_search']."?id_ev=".$row['id']."'>GO</a></td></tr>";
}

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

$color = "";
  $str_etat = "";

  $req_et="SELECT * FROM etats ORDER BY ordo ASC";
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
    $str_etat .= "<option value='".$row_et['id']."' $select_etat>".$row_et['name']."</option>";


  }
  $str_etat .= "</select>";
  $str_etat_def = "<select name='etat' style='background-color:#".$color."'>".$str_etat;


  $str_main .= "<form action ='tr_upd.php'  method='post'>";
  $str_main .= "

  <tr id='i".$row_m['id']."'>
    <td>".$row_m['id']."</td>
    <td>".date('d/m/Y', $row_m['time_beg'] )."<br/>".$row_m['title_event']."<br/><a href='index.php?id_ev=".$row_m['id']."'>[DETAILS]</a></td>
    <td><input type='text' name='name' value=\"".$row_m['name']."\"><br/><input type='hidden' name='id' value='".$row_m['id']."'>
    <input type='text' name='mail' value='".$row_m['mail']."'><br/>
    <input type='text' name='tel' value='".$row_m['tel']."'></td>
    <td>".$str_etat_def."</td>
    <td>".$str_types."</td>
    <td>
      <textarea name ='comment'>".stripslashes($row_m['comment'])."</textarea>
    ";
    if($row_m['time_rappel']>1)
    {
      $val_rappel = $row_m['time_rappel'];}
    else {
      $val_rappel = "";
    }
    $str_main .= "

    <br/>Date relance <input type='date' name='time_rdv' value='".date('Y-m-d', $val_rappel)."'></td>
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

    $str_main .= "<br/><a href='./del_line.php?id=".$row_m['id']."&val=".$val_archive."'>";

    $str_main .= $str_main_ar_txt."</a><br/><a href='./del_def.php?id=".$row_m['id']."'>SUPPRIMER</a></td>
  </tr>";
  $str_main .= "</form>";
}

// popup suivi event

if(is_numeric($_GET['id_ev']))
{
  $req_data_event ="SELECT * FROM `prospects` WHERE `id`='".$_GET['id_ev']."'";
  $res_data_event  = mysqli_query($link, $req_data_event);
  $row_data_event  = mysqli_fetch_assoc($res_data_event);
  if(!empty($row_data_event['comment'])){$val_comment =$row_data_event['comment'];}else{$val_comment ="";}
if(!empty($row_data_event['espaces'])){$val_espaces =$row_data_event['espaces'];}else{$val_espaces ="Nef :
Parvis :
Place du village (salle cantine) :
Terrasse :
Cantine :
Bus cantine :
Mezzanine :
Dôme :
Labx :
Place Pierre Cétois : attention DMP à poser ";}
if(!empty($row_data_event['todo-before'])){$val_todo_before =$row_data_event['todo-before'];}else{$val_todo_before ="";}
if(!empty($row_data_event['todo-after'])){$val_todo_after =$row_data_event['todo-after'];}else{$val_todo_after ="";}
if(!empty($row_data_event['comment_deroule'])){$val_comment_deroule =$row_data_event['comment_deroule'];}else{$val_comment_deroule ="";}
if(!empty($row_data_event['comment_resto_public'])){$val_comment_resto_public =$row_data_event['comment_resto_public'];}else{$val_comment_resto_public ="Carné et/ou VG :
Formules :
Autres spécificités :
Tarifs : ";}
if(!empty($row_data_event['comment_resto_cater'])){$val_comment_resto_cater =$row_data_event['comment_resto_cater'];}else{$val_comment_resto_cater ="";}
if(!empty($row_data_event['com_comm'])){$val_com_comm =$row_data_event['com_comm'];}else{$val_com_comm ="";}
if(!empty($row_data_event['mo_bene_etat'])){$val_mo_bene_etat =$row_data_event['mo_bene_etat'];}
if(!empty($row_data_event['mo_salaries_etat'])){$val_mo_salaries_etat =$row_data_event['mo_salaries_etat'];}
if(!empty($row_data_event['mo_comm'])){$val_mo_comm =$row_data_event['mo_comm'];}else{$val_mo_comm ="
#POSTES A OCCUPER#
INSTALLATION :
ACCEUIL/BILLETERIE :
BAR :
CUISINE :
PLONGE :
SALLE :
RANGEMENT/VOLANT :

#EFFECTIFS#
Nombres bénévoles :
Nombres salariés :
Autres :

#SERVICES EXTERNES#
Sécu :
Régisseur :
Poste de secours :
";}
if(!empty($row_data_event['liste_courses'])){$val_liste_courses =$row_data_event['liste_courses'];}else{$val_liste_courses ="";}
if(!empty($row_data_event['desc_gene'])){$val_desc_gene =$row_data_event['desc_gene'];}else{$val_desc_gene ="Programmation :";}
if(!empty($row_data_event['lien_plan'])){$val_lien_plan =$row_data_event['lien_plan'];}
if(!empty($row_data_event['ferm_ateliers'])){$val_ferm_ateliers =$row_data_event['ferm_ateliers'];}else{$val_ferm_ateliers ="Auto :
Vélo :
Cantine :";}
if(!empty($row_data_event['mobilier'])){$val_mobilier =$row_data_event['mobilier'];}else{$val_mobilier ="Chaises :
Tables rectangulaires :
Tables rondes :
Autres :";}
if(!empty($row_data_event['mat_tech'])){$val_mat_tech =$row_data_event['mat_tech'];}else{$val_mat_tech ="Rallonges :
Multiprises :
Scène :
Sono :
Micro :
Lumière :
Barrières :
Écran :
Chauffages :
Tireuses mobiles :
Trousses de secours : complète ? ";}
if(!empty($row_data_event['vehicules'])){$val_vehicules  =$row_data_event['vehicules'];}else{$val_vehicules  ="Saxo :
Camion Rouge :
Kangoo :
206 :
Scenic";}



  $str_form_beg = "<form action='?id_ev=".$_GET['id_ev']."' method='post' enctype='multipart/form-data'><input type='hidden' name='id' value='".$row_data_event['id']."'>";
  //$str_pop .= "<h1>".$row_data_event['title_event']."</h1>";
  if((!empty($row_data_event['tel']))&&(!empty($row_data_event['mail']))&&(!empty($row_data_event['name'])))
  {
    $col_donnees ="#a6ff7b";
  }
  else{
    $col_donnees ="#ff7b7b";
  }




  $str_donnees .= "<tr><td ist'><span style='color:red;'>Titre PUBLIC</span></td><td> <input type='text' name='title_event' value=\"".$row_data_event['title_event']."\"></td></tr>";
  $str_donnees .= "<tr><td><span style='color:red;'>Sous-titre PUBLIC</span></td><td> <input type='text' name='subtitle_public' value=\"".$row_data_event['subtitle_public']."\"></td></tr>";
  if($row_data_event['archive'] == 0)
  {
    $str_text_etat = "<b style='color:green;'> VISIBLE</b>";
    $str_main_ar_txt = "[masquer]";
    $val_archive = 1;
  }
  else
  {
    $str_text_etat = "<b style='color:red;'> MASQU&Eacute;</b>";
    $str_main_ar_txt = "[afficher]";
    $val_archive = 0;
  }
  $str_donnees .= "<tr><td><span style='color:red;'>".$str_text_etat."</td><td><a href='./del_line.php?id=".$row_data_event['id']."&val=".$val_archive."'>".$str_main_ar_txt."</a></td></tr>";
  $str_donnees .= "<tr><td><span style='color:red;'>lien vers event PUBLIC (si rempli, lien sur home page pointe vers ça et n'ouvre pas popup description)</span></td><td> <input type='text' name='link_event' value='".$row_data_event['link_event']."'></td></tr>";
  $str_donnees .= "<tr><td>Desc géné</td><td><textarea name ='desc_gene'>".$val_desc_gene."</textarea></td></tr>";
  $str_donnees .= "<tr><td><span style='color:red;'>Desc PUBLIC</span></td><td><textarea name ='desc_gene_public'>".$row_data_event['desc_gene_public']."</textarea></td></tr>";
  $str_donnees .= "<tr><td><span style='color:red;'>Programme</span></td><td><textarea name ='programme'>".$row_data_event['programme']."</textarea></td></tr>";

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
  $str_donnees .= "<tr><td>Date début accueil public</td><td> <input type='date' name='time_beg' ".$val_time_beg."></td></tr>";
  $str_donnees .= "<tr><td>Date fin accueil public</td><td> <input type='date' name='time_end' ".$val_time_end."></td></tr>";
  $str_donnees .= "<tr><td class='tabcol_list'><span style='color:red;'>Horaires : </span></td><td> <input type='text' name='horaires' value='".$row_data_event['horaires']."'></td></tr>";
  $str_donnees .= "<tr><td><span style='color:red;'>Image</span></td><td><img src='../../img/".$row_data_event['img_public']."' width='50px'><input type='file' name='fileToUpload' /></td></tr>";
  $str_donnees .= "<tr><td><span style='color:red;'>logo</span></td><td><img src='../../img/".$row_data_event['img_logo']."' width='50px'><input type='file' name='fileToUpload_2' /></td></tr>";
  $str_donnees .= "<tr><td>Contact</td><td> <input type='text' name='name' value=\"".$row_data_event['name']."\"></td></tr>";
  $str_donnees .= "<tr><td>Tel</td><td> <input type='text' name='tel' value='".$row_data_event['tel']."'></td></tr>";
  $str_donnees .= "<tr><td>Mail</td><td> <input type='text' name='mail' value='".$row_data_event['mail']."'></td></tr>";

  $req_data_event_statut ="SELECT * FROM `etats` ORDER BY `ordo` ASC";
  $res_data_event_statut  = mysqli_query($link, $req_data_event_statut);
  while($row_data_event_statut  = mysqli_fetch_assoc($res_data_event_statut))
  {
    if($row_data_event_statut['id'] == $row_data_event['fkid_etat'])
    {
      $selected_str = "selected";
    }
    else
    {
      $selected_str = "";
    }

    $str_etats .= "<option value='".$row_data_event_statut['id']."' ".$selected_str.">".$row_data_event_statut['name']."</option>";
  }

  $str_donnees .= "<tr><td>Statut validation</td><td><select name='fkid_etat'>".$str_etats."</select></td></tr>";

  $str_donnees .= "<tr><td>Lien dossier</td><td> <input type='text' name='link_devis' value='".$row_data_event['link_devis']."'></td></tr>";
  $req_data_event_operateur ="SELECT * FROM `operateurs` ORDER BY `name` ASC";
  $res_data_event_operateur  = mysqli_query($link, $req_data_event_operateur);
  while($row_data_event_operateur  = mysqli_fetch_assoc($res_data_event_operateur))
  {
    if($row_data_event_operateur['id'] == $row_data_event['fkid_operateur'])
    {
      $selected_str = "selected";
    }
    else
    {
      $selected_str = "";
    }

    if($row_data_event_operateur['id'] == $row_data_event['fkid_operateur_dday'])
    {
      $selected_str_dday = "selected";
    }
    else
    {
      $selected_str_dday = "";
    }

    $str_operateur .= "<option value='".$row_data_event_operateur['id']."' ".$selected_str.">".$row_data_event_operateur['name']."</option>";
    $str_operateur_dday .= "<option value='".$row_data_event_operateur['id']."' ".$selected_str_dday.">".$row_data_event_operateur['name']."</option>";

  }
  $str_donnees .= "<tr><td>Todo-list avant</td><td><textarea name ='todo-before'>".$val_todo_before."</textarea></td></tr>";
  $str_donnees .= "<tr><td>Déroulé pendant accueil public</td><td><textarea name ='comment_deroule'>".$val_comment_deroule."</textarea></td></tr>";
  $str_donnees .= "<tr><td>To-do list après</td><td><textarea name ='todo-after'>".$val_todo_after."</textarea></td></tr>";

  //$str_donnees .= "</table>";
  if($val_mo_bene_etat+$val_mo_salaries_etat == 4)
  {
    $col_mo ="#a6ff7b";
  }
  if($val_mo_bene_etat+$val_mo_salaries_etat == 3){
    $col_mo ="#ffee7b";
  }
if($val_mo_bene_etat+$val_mo_salaries_etat < 3){
    $col_mo ="#ff7b7b";
  }

  //$str_mo .= "<tr><td   colspan='2'><h3>MAIN D'OEUVRE</h3></td></tr>";
  $str_mo .= "<tr><td>Référent général</td><td><select name='fkid_operateur'>".$str_operateur."</select></td></tr>";
  $str_mo .= "<tr><td>Référent Jour J</td><td><select name='fkid_operateur_dday'>".$str_operateur_dday."</select></td></tr>";



  if($val_mo_bene_etat=='1'){
    $select_mo_bene_etat_1 = "selected";
  }
  if($val_mo_bene_etat=='2'){
    $select_mo_bene_etat_2 = "selected";
  }



    $str_mo .= "<tr><td>Bénévoles*</td><td><select name='mo_bene_etat'>
    <option value = '1' ".$select_mo_bene_etat_1.">à trouver</option>
    <option value = '2' ".$select_mo_bene_etat_2.">trouvés</option>
    </select></td></tr>";

    if($val_mo_salaries_etat=='1'){
      $select_mo_salaries_etat_1 = "selected";
    }
    if($val_mo_salaries_etat=='2'){
      $select_mo_salaries_etat_2 = "selected";
    }

      $str_mo .= "<tr><td>Salariés*</td><td><select name='mo_salaries_etat'>
      <option value = '1' ".$select_mo_salaries_etat_1.">à trouver</option>
      <option value = '2' ".$select_mo_salaries_etat_2.">trouvés</option>
      </select></td></tr>";
      $str_mo .= "<tr><td>Commentaires MO</td><td><textarea name ='mo_comm'>".$val_mo_comm."</textarea></td></tr>";


  if(($row_data_event['time_beg'] <1)||($row_data_event['time_end'] <1)||(empty($val_espaces)))
  {
    $col_regie ="#ff7b7b";
  }
  else
  {
    $col_regie ="#a6ff7b";
  }
  //$str_pop .= "<table border='1' class='".$col."'>";
  //$str_pop .= "<tr><td colspan='2'><h3>R&Eacute;GIE / ESPACES</h3></td></tr>";
  $str_regie .= "<tr><td>Jauge totale estimée</td><td> <input type='text' name='jauge_ttl' value='".$row_data_event['jauge_ttl']."'></td></tr>";
  $str_regie .= "<tr><td>Jauge simultanée estimée</td><td> <input type='text' name='jauge_simul' value='".$row_data_event['jauge_simul']."'></td></tr>";
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
  $str_regie .= "<tr><td>Date début accueil public</td><td> <input type='date' name='time_beg' ".$val_time_beg."></td></tr>";
  $str_regie .= "<tr><td>Date fin accueil public</td><td> <input type='date' name='time_end' ".$val_time_end."></td></tr>";
  $str_regie .= "<tr><td>Espaces mobilisés</td><td><textarea name ='espaces'>".$val_espaces."</textarea></td></tr>";
  $str_regie .= "<tr><td>Lien plan</td><td><textarea name ='lien_plan'>".$val_lien_plan."</textarea></td></tr>";
  $str_regie .= "<tr><td>Fermeture ateliers</td><td><textarea name ='ferm_ateliers'>".$val_ferm_ateliers."</textarea></td></tr>";
  $str_regie .= "<tr><td>Mobilier</td><td><textarea name ='mobilier'>".$val_mobilier."</textarea></td></tr>";
  $str_regie .= "<tr><td>Matériel technique</td><td><textarea name ='mat_tech'>".$val_mat_tech."</textarea></td></tr>";
  $str_regie .= "<tr><td>Vehicules</td><td><textarea name ='vehicules'>".$val_vehicules."</textarea></td></tr>";
  //$str_regie .= "</table>";
  if((empty($val_comment_resto_public))||(empty($val_comment_resto_cater))||(empty($val_liste_courses)))
  {
    $col_resto ="#ff7b7b";
  }
  else
  {
    $col_resto ="#a6ff7b";
  }
  //$str_regie .= "<table border='1' class='".$col."'>";
  //$str_regie .= "<tr><td colspan='2'><h3>RESTAURATION</h3></td></tr>";
  $str_resto .= "<tr><td>Besoin resto public*</td><td><textarea name ='comment_resto_public'>".$val_comment_resto_public."</textarea></td></tr>";
  $str_resto .= "<tr><td>Besoin resto catering*</td><td><textarea name ='comment_resto_cater'>".$val_comment_resto_cater."</textarea></td></tr>";
  $str_resto .= "<tr><td>Courses spécifiques*</td><td><textarea name ='liste_courses'>".$val_liste_courses."</textarea></td></tr>";

  //$str_regie .= "</table>";
  //$str_regie .= "<table border='1'>";
  if($row_data_event['com_etat']=='1')
  {
    $col_com ="#ff7b7b";
  }
  if($row_data_event['com_etat']=='2')
  {
    $col_com ="#ffee7b";
  }
  if($row_data_event['com_etat']=='3')
  {
    $col_com ="#a6ff7b";
  }

    //$str_regie.= "<table border='1' class='".$col."'>";
    //$str_pop .= "<tr><td colspan='2'><h3>COMMUNICATION</h3></td></tr>";
    if($row_data_event['com_etat']=='1'){
      $select_com_etat_1 = "selected";
    }
    else
    {
      $select_com_etat_1 = "";
    }
    if($row_data_event['com_etat']=='2'){
      $select_com_etat_2 = "selected";
    }
    else
    {
      $select_com_etat_2 = "";
    }
    if($row_data_event['com_etat']=='3'){
      $select_com_etat_3 = "selected";
    }
    else
    {
      $select_com_etat_3 = "";
    }
      $str_com .= "<tr><td>Etat</td><td><select name='com_etat'>
      <option value = '1' ".$select_com_etat_1.">à faire</option>
      <option value = '2' ".$select_com_etat_2.">éléments reçus</option>
      <option value = '3' ".$select_com_etat_3.">fait</option>
      </select></td></tr>";
      $str_com .= "<tr><td>TO DO Com</td><td><textarea name ='com_comm'>".$val_com_comm."</textarea></td></tr>";
      $str_com .= "<tr><td>Lien ressources Com</td><td> <input type='text' name='com_lien' value='".$row_data_event['com_lien']."'></td></tr>";

      $str_pop .= "<tr><td colspan='2'></td></tr>";

  //$str_form_end .= "</table>";
  $str_form_end .= "<br/><input type='submit' value='VALIDER LES CHANGEMENTS' width='400px'>";

  $str_form_end .= "</form>";
}


$str_tab_before = "
<table data-role='table' id='movie-table-custom' data-mode='reflow' class='movie-list ui-responsive'>
<thead>
  <tr>
    <th  style='width:20%' data-priority='persist'></th>
    <th  style='width:80%' data-priority='persist'></th>
  </tr>
</thead>
<tbody>";
$str_tab_after = "</tbody></table>";
?>
<html>
<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <title>Evenementor | <?=$row_data_event['title_event']?></title>
   <link rel="stylesheet" href="../jquery/css/themes/default/jquery.mobile-1.4.5.min.css">
   <link rel="stylesheet" href="../jquery/_assets/css/jqm-demos.css">

   <script src="../jquery/js/jquery.js"></script>
  <script src="../jquery/_assets/js/index.js"></script>
  <script src="../jquery/js/jquery.mobile-1.4.5.min.js"></script>
  <script>
  var form = new FormData($("#uploadForm")[0]);
$.ajax({
      url: valid_event.php,
      method: "POST",
      dataType: 'json',
      data: form,
      processData: false,
      contentType: false,
      success: function(result){},
      error: function(er){}
});
  </script>

<style>

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
td{
  border-bottom: solid thin #000000;
  font-size: 15;
  font-weight: 700;
}
</style>
</head>
<body>
  <div data-role="header" data-theme="b">
    <h1>Evenementor | <?=$row_data_event['title_event']?></h1>
     <a href="./index_m.php?id_ev=<?=$row_data_event['id']?>" target="blank" class="ui-btn-right ui-btn ui-btn-inline ui-mini ui-corner-all ui-btn-icon-right ui-icon-bars">VERSION AFFICHAGE</a>
  </div>

   <div data-role="tabs" id="tabs">
     <div data-role="navbar">
       <ul>
         <li><a data-icon="bullets" style="border-bottom: <?=$col_donnees?> thick solid;" target="_self" href="#one" data-ajax="false">DONNEES</a></li>
         <li><a data-icon="user" style="border-bottom: <?=$col_mo?> thick solid;" target="_self" href="#two" data-ajax="false">MAIN D'OEUVRE</a></li>
         <li><a data-icon="action" style="border-bottom: <?=$col_regie?> thick solid;" target="_self" href="#three" data-ajax="false">REGIE</a></li>
         <li><a data-icon="shop" style="border-bottom: <?=$col_resto?> thick solid;" target="_self" href="#four" data-ajax="false">RESTAURATION</a></li>
         <li><a data-icon="navigation" style="border-bottom: <?=$col_com?> thick solid;" target="_self" href="#five" data-ajax="false">COM</a></li>
       </ul>
     </div>
     <?=$str_form_beg?>
     <div id="one" class="ui-body-d ui-content" style="background-color: <?=$col_donnees?>;">
       <h1>DONNEES</h1><?=$str_tab_before.$str_donnees.$str_tab_after?>
     </div>
     <div id="two" class="ui-body-d ui-content" style="background-color: <?=$col_mo?>;">
       <h1>MAIN D'OEUVRE</h1><?=$str_tab_before.$str_mo.$str_tab_after?>
     </div>
     <div id="three" class="ui-body-d ui-content" style="background-color: <?=$col_regie?>;">
       <h1>REGIE</h1><?=$str_tab_before.$str_regie.$str_tab_after?>
     </div>
     <div id="four" class="ui-body-d ui-content" style="background-color: <?=$col_resto?>;">
       <h1>RESTAURATION</h1><?=$str_tab_before.$str_resto.$str_tab_after?>
     </div>
     <div id="five" class="ui-body-d ui-content" style="background-color: <?=$col_com?>;">
       <h1>COM</h1><?=$str_tab_before.$str_com.$str_tab_after?>
     </div>
     <?=$str_form_end?>
     <?=$req_ins?>
   </div>
</body>
</html>
