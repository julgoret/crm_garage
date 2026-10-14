<?php
ini_set('display_errors', 0);
include_once('./connect_inc.php');


// passage en session de la dernière requète pour ne pas perdre la recherche
if(isset($_GET['search_types']) && isset($_GET['archives'])){
  $_SESSION['last_search'] = "?search_types=".$_GET['search_types']."&archives=".$_GET['archives'];
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
if((isset($_GET['search_types']))OR(isset($_GET['archives'])))
{
  if($_GET['search_types']>0){

  $endreq .= " AND fkid_type ='".$_GET['search_types']."'";
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





$req_m="SELECT * FROM prospects WHERE id>0 ".$endreq." ORDER BY `time_beg` ASC";

$res_m  = mysqli_query($link, $req_m);
if(mysqli_num_rows($res_m)==0){//header('location:index.php');
}
//echo $req_m;
while($row_m  = mysqli_fetch_assoc($res_m))
{
  // on extrait le type
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


  $str_main .= "<form action ='tr_upd.php'  method='post'>";
  $str_main .= "

  <tr id='i".$row_m['id']."'>
    <td>".$row_m['id']."</td>
    <td>".date('d/m/Y', $row_m['time_beg'] )."<br/>".$row_m['title_event']."<br/><a href='index_light.php?id_ev=".$row_m['id']."'>[MODIFIER]</a>";
  if($row_m['time_beg'] > 1)
  {
    $str_main .= "<br/><a href='plan.php?date=".date('Y-m-d', $row_m['time_beg'])."'>[PLAN DU JOUR]</a>";
  }
  $str_main .= "</td>
    <td>".$str_types."</td>
    <td>
      <input type='hidden' name='id' value='".$row_m['id']."'>
      <input type='hidden' name='name' value=\"".$row_m['name']."\">
      <input type='hidden' name='mail' value='".$row_m['mail']."'>
      <input type='hidden' name='tel' value='".$row_m['tel']."'>
      <input type='hidden' name='etat' value='".$row_m['fkid_etat']."'>
      <textarea name ='comment'>".stripslashes($row_m['comment'])."</textarea>
    ";
    if($row_m['time_rappel']>1)
    {
      $val_rappel = $row_m['time_rappel'];}
    else {
      $val_rappel = "";
    }
    $str_main .= "

    <br/>Date relance <input type='text' name='time_rdv' value='".date('Y-m-d', $val_rappel)."'></td>
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

  // champ categ
  $req_categ_event ="SELECT * FROM `categ_com`";
  $res_categ_event  = mysqli_query($link, $req_categ_event);
  while($row_categ_event  = mysqli_fetch_assoc($res_categ_event))
  {
    if($row_categ_event['id']==$row_data_event['id_com_categ'])
    {
      $chk_categ = "selected";
    }
    else {
      $chk_categ = "";
    }
    $str_categ_ev .= "<option ".$chk_categ." value='".$row_categ_event['id']."'>".$row_categ_event['nom']."</option>";
  }

  if(!empty($row_data_event['comment'])){$val_comment =$row_data_event['comment'];}else{$val_comment ="";}
if(!empty($row_data_event['espaces'])){$val_espaces =$row_data_event['espaces'];}else{$val_espaces ="
[ ] Sécu
[ ] Régisseur
[ ] Poste de secours
[ ] Scène
[ ] IDDAC
[ ] Sono
[ ] Lumière
[ ] Mobilier ";}
if(!empty($row_data_event['jauge_ttl'])){$val_jauge =$row_data_event['jauge_ttl'];}else{$val_jauge ="
Total :
Places assises :
Places debout : ";}
if(!empty($row_data_event['moyens_pay'])){$val_moyens_pay =$row_data_event['moyens_pay'];}else{$val_moyens_pay ="
CAISSES :
TPE :
SUM UP :
BAR UTILISE : ";}

if(!empty($row_data_event['todo-before'])){$val_todo_before =$row_data_event['todo-before'];}else{$val_todo_before ="";}
if(!empty($row_data_event['todo-after'])){$val_todo_after =$row_data_event['todo-after'];}else{$val_todo_after ="";}
if(!empty($row_data_event['comment_deroule'])){$val_comment_deroule =$row_data_event['comment_deroule'];}else{$val_comment_deroule ="";}
if(!empty($row_data_event['comment_resto_public'])){$val_comment_resto_public =$row_data_event['comment_resto_public'];}else{$val_comment_resto_public ="Carné et/ou VG :
Tarifs : ";}
if(!empty($row_data_event['comment_resto_cater'])){$val_comment_resto_cater =$row_data_event['comment_resto_cater'];}else{$val_comment_resto_cater ="";}
if(!empty($row_data_event['com_comm'])){$val_com_comm =$row_data_event['com_comm'];}else{$val_com_comm ="";}
if(!empty($row_data_event['mo_bene_etat'])){$val_mo_bene_etat =$row_data_event['mo_bene_etat'];}
if(!empty($row_data_event['mo_salaries_etat'])){$val_mo_salaries_etat =$row_data_event['mo_salaries_etat'];}
if(!empty($row_data_event['mo_comm'])){$val_mo_comm =$row_data_event['mo_comm'];}else{$val_mo_comm ="";}
if(!empty($row_data_event['liste_courses'])){$val_liste_courses =$row_data_event['liste_courses'];}else{$val_liste_courses ="";}
if(!empty($row_data_event['desc_gene'])){$val_desc_gene =$row_data_event['desc_gene'];}else{$val_desc_gene ="Programmation :";}
if(!empty($row_data_event['lien_plan'])){$val_lien_plan =$row_data_event['lien_plan'];}
if(!empty($row_data_event['ferm_ateliers'])){$val_ferm_ateliers =$row_data_event['ferm_ateliers'];}else{$val_ferm_ateliers ="Auto :
Vélo :
Cantine :";}
if(!empty($row_data_event['mobilier'])){$val_mobilier =$row_data_event['mobilier'];}else{$val_mobilier ="Chaises :
Tables rectangulaires :
Tables rondes :
Autres :";}
if(!empty($row_data_event['mat_tech'])){$val_mat_tech =$row_data_event['mat_tech'];}else{$val_mat_tech ="Rallonges :
Multiprises :
Scène :
Sono :
Micro :
Lumière :
Barrières :
Écran :
Chauffages :
Tireuses mobiles :
Trousses de secours : complète ? ";}
if(!empty($row_data_event['vehicules'])){$val_vehicules  =$row_data_event['vehicules'];}else{$val_vehicules  ="Saxo :
Camion Rouge :
Kangoo :
206 :
Scenic";}

  $str_pop = "<div class='pop'>";
  $str_pop .= "<div style='float:right;padding: 5px;'><a href='./index_light.php".$_SESSION['last_search']."'><b>[FERMER]</b></a><a href='./printable_todo_light.php?id_ev=".$row_data_event['id']."'><b>[IMPRIMER]</b></a>";
  if($row_data_event['time_beg'] > 1)
  {
    $str_pop .= "<a href='./plan.php?date=".date('Y-m-d', $row_data_event['time_beg'])."'><b>[PLAN DU JOUR]</b></a>";
  }
  $str_pop .= "</div>";

  $str_pop .= "<div style='float:left;padding: 5px;'>";
  $str_pop .= "<form action='valid_event_light_2.php' method='post' enctype='multipart/form-data'><input type='hidden' name='id' value='".$row_data_event['id']."'>";
  $str_pop .= "<h1>".$row_data_event['title_event']."</h1>";
  if((!empty($row_data_event['tel']))&&(!empty($row_data_event['mail']))&&(!empty($row_data_event['name'])))
  {
    $col ="green";
  }
  else{
    $col ="red";
  }

  $str_types = "<select id='fO' name='fkid_type' onchange='document.getElementById(\"backid\").value=\"f0\";this.form.submit()'>";
  $req_ty="SELECT * FROM types";
  $res_ty  = mysqli_query($link, $req_ty);
  while($row_ty  = mysqli_fetch_assoc($res_ty))
  {
    if($row_ty['id'] == $row_data_event['fkid_type']) {
      $select_type = "selected";
    }
    else {
      $select_type = "";
    }
    $str_types .= "<option value='".$row_ty['id']."' $select_type>".$row_ty['name']."</option>";


  }
  $str_types .= "</select>";

  $str_pop .= "<br/>".$str_types;

  $str_pop .= "<table border='1' class='".$col."'>";
  $str_pop .= "<tr><td class='tabcol' colspan='2'><h3>DONN&Eacute;S événement</h3></td></tr>";


  if($row_data_event['time_beg']>0)
  {
    $val_time_beg = "value='".date("Y-m-d", $row_data_event['time_beg'])."'";
  }
  else
  {
    $val_time_beg = "value='".date("Y-m-d", time())."'";
  }
  if($row_data_event['time_end']>0)
  {
    $val_time_end = "value='".date("Y-m-d", $row_data_event['time_end'])."'";
  }
  else
  {
    $val_time_end = "value='".date("Y-m-d", time())."'";
  }

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

  $str_pop .= "<tr><td class='tabcol_list'><span>Titre événent</span></td><td>";

  $search_types ="";
  $str_types = "<select name='types'>";



  $str_pop .= " <textarea rows='2' name='title_event'  id='f1' onchange='document.getElementById(\"backid\").value=\"f1\";this.form.submit()'>".$row_data_event['title_event']."</textarea></td></tr>";
  //$str_pop .= "<tr><td>Desc géné</td><td><textarea name ='desc_gene'>".$val_desc_gene."</textarea></td></tr>";
  $str_pop .= "<tr><td>Date début accueil public</td><td> <input id='f2' onchange='document.getElementById(\"backid\").value=\"f2\";this.form.submit()' type='date' name='time_beg' ".$val_time_beg."></td></tr>";
  $str_pop .= "<tr><td>Date fin accueil public</td><td> <input id='f3' onchange='document.getElementById(\"backid\").value=\"f3\";this.form.submit()' type='date' name='time_end' ".$val_time_end."></td></tr>";
  $str_pop .= "<tr><td class='tabcol_list'><span style='color:red;'>Horaires : </span></td><td><textarea id='f4' onchange='document.getElementById(\"backid\").value=\"f4\";this.form.submit()' rows='2' name='horaires'>".$row_data_event['horaires']."</textarea></td></tr>";
  $str_pop .= "<tr><td>Organisateur (Structure + référent)</td><td> <input  id='f5' onchange='document.getElementById(\"backid\").value=\"f5\";this.form.submit()' type='text' name='name' value=\"".$row_data_event['name']."\"></td></tr>";
  $str_pop .= "<tr><td>Tel</td><td> <input id='f6' onchange='document.getElementById(\"backid\").value=\"f6\";this.form.submit()' type='text' name='tel' value='".$row_data_event['tel']."'></td></tr>";
  $str_pop .= "<tr><td>Mail</td><td> <input id='f7' onchange='document.getElementById(\"backid\").value=\"f7\";this.form.submit()' type='text' name='mail' value='".$row_data_event['mail']."'></td></tr>";
  $str_pop .= "<tr><td>Lien dossier</td><td> <input id='f8' onchange='document.getElementById(\"backid\").value=\"f8\";this.form.submit()' type='text' name='link_devis' value='".$row_data_event['link_devis']."'></td></tr>";
  $str_pop .= "<tr><td>Déroulé </td><td><textarea id='f99' onchange='document.getElementById(\"backid\").value=\"f99\";this.form.submit()' name ='comment_deroule'>".$val_comment_deroule."</textarea></td></tr>";

  //$str_pop .= "<tr><td>Todo-list avant</td><td><textarea name ='todo-before'>".$val_todo_before."</textarea></td></tr>";
  //$str_pop .= "<tr><td>Déroulé pendant accueil public</td><td><textarea name ='comment_deroule'>".$val_comment_deroule."</textarea></td></tr>";
  //$str_pop .= "<tr><td>To-do list après</td><td><textarea name ='todo-after'>".$val_todo_after."</textarea></td></tr>";

  $str_pop .= "</table>";
  if(!empty($val_mo_comm))
  {
    $col ="green";
  }
  else {
    $col ="red";
  }
  $str_pop .= "<table border='1' class='".$col."'>";
  $str_pop .= "<tr><td  class='tabcol'  colspan='2'><h3>MAIN D'OEUVRE</h3></td></tr>";
  $str_pop .= "<tr><td class='tabcol_list'>Référent général</td><td><select id='f9' onchange='document.getElementById(\"backid\").value=\"f9\";this.form.submit()' name='fkid_operateur'>".$str_operateur."</select></td></tr>";
  $str_pop .= "<tr><td>Référent Jour J</td><td><select id='f10' onchange='document.getElementById(\"backid\").value=\"f10\";this.form.submit()' name='fkid_operateur_dday'>".$str_operateur_dday."</select></td></tr>";


      $str_pop .= "<tr><td>Main d'oeuvre</td><td><textarea id='f11' onchange='document.getElementById(\"backid\").value=\"f11\";this.form.submit()' name ='mo_comm'>".$val_mo_comm."</textarea></td></tr>";

  $str_pop .= "</table>";

  if(($row_data_event['time_beg'] <1)||($row_data_event['time_end'] <1)||(empty($val_espaces)))
  {
    $col ="red";
  }
  else
  {
    $col ="green";
  }
  $str_pop .= "<table border='1' class='".$col."'>";
  $str_pop .= "<tr><td class='tabcol' colspan='2'><h3>R&Eacute;GIE / ESPACES</h3></td></tr>";
  $str_pop .= "<tr><td class='tabcol_list'>Jauge estimée</td>";
  $str_pop .= "<td><textarea id='f12' onchange='document.getElementById(\"backid\").value=\"f12\";this.form.submit()' name ='jauge_ttl'>".$val_jauge."</textarea></td></tr>";
  $str_pop .= "<tr><td>Régie</td><td><textarea id='f13' onchange='document.getElementById(\"backid\").value=\"f13\";this.form.submit()' name ='espaces'>".$val_espaces."</textarea></td></tr>";
  $str_pop .= "</table>";
  if((empty($val_comment_resto_public))||(empty($val_comment_resto_cater))||(empty($val_liste_courses)))
  {
    $col ="red";
  }
  else
  {
    $col ="green";
  }
  $str_pop .= "<table border='1' class='".$col."'>";
  $str_pop .= "<tr><td class='tabcol' colspan='2'><h3>RESTAURATION</h3></td></tr>";
  $str_pop .= "<tr><td class='tabcol_list'>Besoin resto public*</td><td><textarea id='f14' onchange='document.getElementById(\"backid\").value=\"f14\";this.form.submit()' name ='comment_resto_public'>".$val_comment_resto_public."</textarea></td></tr>";
  $str_pop .= "<tr><td>Besoin resto catering*</td><td><textarea id='f15' onchange='document.getElementById(\"backid\").value=\"f15\";this.form.submit()' name ='comment_resto_cater'>".$val_comment_resto_cater."</textarea></td></tr>";
  $str_pop .= "<tr><td>Courses spécifiques*</td><td><textarea id='f16' onchange='document.getElementById(\"backid\").value=\"f16\";this.form.submit()' name ='liste_courses'>".$val_liste_courses."</textarea></td></tr>";
  $str_pop .= "<tr><td>Moyens paiement</td><td><textarea id='f100' onchange='document.getElementById(\"backid\").value=\"f100\";this.form.submit()' name ='moyens_pay'>".$val_moyens_pay."</textarea></td></tr>";

  $str_pop .= "</table>";
  $str_pop .= "<table border='1'>";
  if($row_data_event['com_etat']=='1')
  {
    $col ="red";
  }
  if($row_data_event['com_etat']=='2')
  {
    $col ="yellow";
  }
  if($row_data_event['com_etat']=='3')
  {
    $col ="green";
  }

    $str_pop .= "<table border='1' class='".$col."'>";
    $str_pop .= "<tr><td class='tabcol' colspan='2'><h3>COMMUNICATION</h3></td></tr>";

      $str_pop .= "<tr><td>Remarques / Contact Com</td><td><textarea  id='f17' onchange='document.getElementById(\"backid\").value=\"f17\";this.form.submit()'  name ='com_comm'>".$val_com_comm."</textarea></td></tr>";
      $str_pop .= "<tr><td>Comptes RS à mentionner</td><td><textarea  id='f101' onchange='document.getElementById(\"backid\").value=\"f101\";this.form.submit()'  name ='comptes_rs'>".$row_data_event['comptes_rs']."</textarea></td></tr>";
      $str_pop .= "<tr><td>Lien ressources Com</td><td> <input id='f18' onchange='document.getElementById(\"backid\").value=\"f18\";this.form.submit()' type='text' name='com_lien' value='".$row_data_event['com_lien']."'></td></tr>";
      $str_pop .= "</table><table border='1' >";
      $str_pop .= "<tr><td colspan='2'><h3>CONTENUS COM SITE WEB</h3></td></tr>";


        $str_pop .= "<tr><td class='tabcol_list'><span>Sous-titre PUBLIC</span></td><td><textarea rows='2' name='subtitle_public' id='f19' onchange='document.getElementById(\"backid\").value=\"f19\";this.form.submit()' >".$row_data_event['subtitle_public']."</textarea></td></tr>";
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
        $str_pop .= "<tr><td class='tabcol_list'><span>".$str_text_etat."</td><td><a href='./del_line.php?id=".$row_data_event['id']."&val=".$val_archive."'>".$str_main_ar_txt."</a></td></tr>";
        $str_pop .= "<tr><td class='tabcol_list'><span>lien vers event PUBLIC (si rempli, lien sur home page pointe vers ça et n'ouvre pas popup description)</span></td><td> <input id='f20' onchange='document.getElementById(\"backid\").value=\"f20\";this.form.submit()' type='text' name='link_event' value='".$row_data_event['link_event']."'></td></tr>";

        $str_pop .= "<tr><td class='tabcol_list'><span>lien vers inscription bénévoles</span></td><td> <input id='f27' onchange='document.getElementById(\"backid\").value=\"f27\";this.form.submit()' type='text' name='link_bene' value='".$row_data_event['link_bene']."'></td></tr>";
        $str_pop .= "<tr><td class='tabcol_list'><span>lien vers inscription billetterie</span></td><td> <input id='f28' onchange='document.getElementById(\"backid\").value=\"f28\";this.form.submit()' type='text' name='link_insc' value='".$row_data_event['link_insc']."'></td></tr>";

        $str_pop .= "<tr><td><span>Desc PUBLIC</span></td><td><textarea id='mytextarea' name ='desc_gene_public' onchange='document.getElementById(\"backid\").value=\"mytextarea\";this.form.submit()' >".$row_data_event['desc_gene_public']."</textarea><br/><input type='submit' value ='valider' ></td></tr>";

      //  $str_pop .= "<br/><select name='categ'>".$str_categ_ev."</select><br/>";
        $str_pop .= "<tr><td><span>Categorie</span></td><td><select  id='f22' onchange='document.getElementById(\"backid\").value=\"f22\";this.form.submit()'  name='categ'>".$str_categ_ev."</select></td></tr>";
        $str_pop .= "<tr><td><span>Programme (pour éditeur de fly uniquement)</span></td><td><textarea id='f23' onchange='document.getElementById(\"backid\").value=\"f23\";this.form.submit()' id='mytextarea'  name ='programme'>".$row_data_event['programme']."</textarea></td></tr>";
        $str_pop .= "<tr><td><span>Image</span><span style='color:red;'><br/>PAS DE TITRAGES DANS LE VISUEL</span></td><td><img src='../../img/".$row_data_event['img_public']."' width='50px'><input type='file' name='fileToUpload'  id='f24' onchange='document.getElementById(\"backid\").value=\"f24\";this.form.submit()'  /></td></tr>";
        $str_pop .= "<tr><td><span>logo</span></td><td><img src='../../img/".$row_data_event['img_logo']."' width='50px'><input type='file' name='fileToUpload_2'  id='f25' onchange='document.getElementById(\"backid\").value=\"f25\";this.form.submit()'  /></td></tr>";
        $str_pop .= "<tr><td class='tabcol_list'><span>couleur : </span></td><td> <input  id='f26' onchange='document.getElementById(\"backid\").value=\"f26\";this.form.submit()'  type='color' name='col' value='".$row_data_event['col']."'></td></tr>";
  $str_pop .= "</table>";
  $str_pop .= "<br/>";
  $str_pop .= "<input type='hidden' value='rien' name='backid' id='backid'>";
  $str_pop .= "</form>";
  $str_pop .= "</div>";
  $str_pop .= "</div>";
}

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
        "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="fr">
  <head>
    <meta charset="utf-8">
    <title>CRM Suivi événements</title>
    <link rel="stylesheet" href="./admin.css">
    <script src="./admin.js?v=1" defer></script>
    <script src="https://cdn.tiny.cloud/1/qeu73mhxuefzsc0qgh0t3velh1upnfz8mthf5wdvb421d3w1/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>
    <script>
      tinymce.init({
        selector: '#mytextarea',
        plugins: [
    'advlist', 'anchor', 'autolink', 'codesample', 'fullscreen', 'help',
    'image', 'tinydrive', 'lists', 'link', 'media', 'preview',
    'searchreplace', 'table', 'template', 'visualblocks', 'wordcount', 'code'
  ],
  toolbar: 'undo redo | bold italic | forecolor backcolor | template codesample | alignleft aligncenter alignright alignjustify | bullist numlist | link image | code',

      });
    </script>
  </head>
  <body>
<?php
echo "<h1>CRM suivi événements Garage Moderne</h1>";
echo $str_pop;
?>
<details class="insert-box">
  <summary>+ Insérer un nouvel événement</summary>
  <form action='tr_insert.php' method='post' class="insert-form">
    <input type='hidden' name='from' value='light'>
    <input type='text' name='name' placeholder='NOM DE L ÉVÉNEMENT'>
    <input type='submit' value ='Insérer'>
  </form>
</details>
<h3>TRIER<h3>
<form action='' method="get">
  <table border="1">
    <tr>

      <td>TYPE : <?=$str_types_search?></td>
      <td>ACTIFS <input type="radio" name="archives" value="0" <?=$selecta0?>><br/>ARCHIVES <input type="radio" name="archives" value="1" <?=$selecta1?>><br/>TOUS <input type="radio" name="archives" value="2" <?=$selecta2?>></td>
      <td><input type='submit' value ='TRIER'></td>
    </tr>
  </table>
</form>
<?php
echo "<table border='1'><tr>
  <td>ID</td>
  <td>&Eacutevénement</td>
  <td>type</td>
  <td>Suivi</td>
</tr>".$str_main."</table>";

?>

</body>
</html>
