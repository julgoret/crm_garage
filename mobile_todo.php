<?php

include_once('./connect_inc.php');

$req_data_event ="SELECT * FROM `prospects` WHERE `id`='".$_GET['id_ev']."'";
$res_data_event  = mysqli_query($link, $req_data_event);
$row_data_event  = mysqli_fetch_assoc($res_data_event);
$str_pop .= "<h1>".$row_data_event['title_event']."</h1>";
if((!empty($row_data_event['tel']))&&(!empty($row_data_event['mail']))&&(!empty($row_data_event['name'])))
{
  $col ="green";
}
else{
  $col ="yellow";
}
$str_pop .= "<img src='../../img/".$row_data_event['img_public']."' height='150px'>";
$str_pop .= "<table border='1' class='".$col."'>";
$str_pop .= "<tr><td class='tabcol' colspan='2'><h2>DONN&Eacute;ES / CONTACTS</h2></td></tr>";
$str_pop .= "<tr><td class='tabcol_list'>Titre</td><td> ".$row_data_event['title_event']."'</td></tr>";
$str_pop .= "<tr><td>Desc géné</td><td>".$row_data_event['desc_gene']."</td></tr>";

$str_pop .= "<tr><td>Contact</td><td>".$row_data_event['name']."</td></tr>";
$str_pop .= "<tr><td>Tel</td><td>".$row_data_event['tel']."</td></tr>";
$str_pop .= "<tr><td>Mail</td><td>".$row_data_event['mail']."</td></tr>";

$req_data_event_statut ="SELECT * FROM `etats`";
$res_data_event_statut  = mysqli_query($link, $req_data_event_statut);
while($row_data_event_statut  = mysqli_fetch_assoc($res_data_event_statut))
{
  if($row_data_event_statut['id'] == $row_data_event['fkid_etat'])
  {
    $selected_str = $row_data_event_statut['name'];
  }

  $str_etats = $selected_str;
}

$str_pop .= "<tr><td>Etat</td><td>".$str_etats."</td></tr>";

$req_data_event_operateur ="SELECT * FROM `operateurs`";
$res_data_event_operateur  = mysqli_query($link, $req_data_event_operateur);
while($row_data_event_operateur  = mysqli_fetch_assoc($res_data_event_operateur))
{
  if($row_data_event_operateur['id'] == $row_data_event['fkid_operateur'])
  {
    $selected_str_prep = $row_data_event_operateur['name'];
  }


  if($row_data_event_operateur['id'] == $row_data_event['fkid_operateur_dday'])
  {
    $selected_str_dday = $row_data_event_operateur['name'];
  }


  $str_operateur = $selected_str_prep;
  $str_operateur_dday = $selected_str_dday;

}
$str_pop .= "<tr><td>Lien devis</td><td>".$row_data_event['link_devis']."</td></tr>";

$str_pop .= "</table>";
if($row_data_event['mo_bene_etat']+$row_data_event['mo_salaries_etat'] == 4)
{
  $col ="green";
}
if($row_data_event['mo_bene_etat']+$row_data_event['mo_salaries_etat'] == 3){
  $col ="yellow";
}
if($row_data_event['mo_bene_etat']+$row_data_event['mo_salaries_etat'] < 3){
  $col ="red";
}
$str_pop .= "<table border='1' class='".$col."'>";
$str_pop .= "<tr><td  class='tabcol'  colspan='2'><h2>MAIN D'OEUVRE</h2></td></tr>";
$str_pop .= "<tr><td class='tabcol_list'>Référent préparation</td><td>".$str_operateur."</td></tr>";

$str_pop .= "<tr><td>Référent Jour J</td><td>".$str_operateur_dday."</td></tr>";



if($row_data_event['mo_bene_etat']=='1'){
  $select_mo_bene_etat = "à trouver";
}
if($row_data_event['mo_bene_etat']=='2'){
  $select_mo_bene_etat = "trouvés";
}



  $str_pop .= "<tr><td>Bénévoles*</td><td>".$select_mo_bene_etat."</td></tr>";

  if($row_data_event['mo_salaries_etat']=='1'){
    $select_mo_salaries_etat = "à trouver";
  }
  if($row_data_event['mo_salaries_etat']=='2'){
    $select_mo_salaries_etat = "trouvés";
  }

    $str_pop .= "<tr><td>Salariés*</td><td>".nl2br($select_mo_salaries_etat)."</td></tr>";
    $str_pop .= "<tr><td>Commentaires MO</td><td>".nl2br($row_data_event['mo_comm'])."</td></tr>";

$str_pop .= "</table>";

if(($row_data_event['time_beg'] <1)||($row_data_event['time_end'] <1)||(empty($row_data_event['espaces'])))
{
  $col ="red";
}
else
{
  $col ="green";
}
$str_pop .= "<table border='1' class='".$col."'>";
$str_pop .= "<tr><td class='tabcol' colspan='2'><h2>R&Eacute;GIE</h2></td></tr>";
$str_pop .= "<tr><td class='tabcol_list'>Jauge totale estimée</td><td>".$row_data_event['jauge_ttl']."</td></tr>";
$str_pop .= "<tr><td>Jauge simultanée estimée</td><td>".$row_data_event['jauge_simul']."</td></tr>";
if($row_data_event['time_beg']>0)
{
  $val_time_beg = date("d/m/Y H:i", $row_data_event['time_beg']);
}
else
{
  $val_time_beg = "";
}
if($row_data_event['time_end']>0)
{
  $val_time_end = date("d/m/Y H:i", $row_data_event['time_end']);
}
else
{
  $val_time_end = "";
}
$str_pop .= "<tr><td>Date et h début*</td><td>".$val_time_beg."</td></tr>";
$str_pop .= "<tr><td>Date et h de fin*</td><td>".$val_time_end."</td></tr>";

$str_pop .= "<tr><td>Espaces mobilisés*</td><td>".nl2br($row_data_event['espaces'])."</td></tr>";
$str_pop .= "<tr><td>Todo-list avant</td><td>".nl2br($row_data_event['todo-before'])."</td></tr>";
$str_pop .= "<tr><td>Déroulé</td><td>".nl2br($row_data_event['comment_deroule'])."</td></tr>";
$str_pop .= "<tr><td>To-do list après</td><td>".nl2br($row_data_event['todo-after'])."</td></tr>";
$str_pop .= "</table>";
if((empty($row_data_event['comment_resto_public']))||(empty($row_data_event['comment_resto_cater']))||(empty($row_data_event['liste_courses'])))
{
  $col ="red";
}
else
{
  $col ="green";
}
$str_pop .= "<table border='1' class='".$col."'>";
$str_pop .= "<tr><td class='tabcol' colspan='2'><h2>RESTAURATION</h2></td></tr>";
$str_pop .= "<tr><td class='tabcol_list'>Besoin resto public*</td><td>".nl2br($row_data_event['comment_resto_public'])."</td></tr>";
$str_pop .= "<tr><td>Besoin resto catering*</td><td>".nl2br($row_data_event['comment_resto_cater'])."</td></tr>";
$str_pop .= "<tr><td>Courses spécifiques*</td><td>".nl2br($row_data_event['liste_courses'])."</td></tr>";

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
$str_pop .= "<tr><td class='tabcol' colspan='2'><h2>COMMUNICATION</h2></td></tr>";
if($row_data_event['com_etat']=='1'){
  $select_com_etat = "à faire";
}
if($row_data_event['com_etat']=='2'){
  $select_com_etat = "éléments reçus";
}
if($row_data_event['com_etat']=='3'){
  $select_com_etat = "fait";
}

  $str_pop .= "<tr><td class='tabcol_list'>Etat</td><td>".$select_com_etat."</td></tr>";
  $str_pop .= "<tr><td>Commentaires Com</td><td>".nl2br($row_data_event['com_comm'])."</td></tr>";
  $str_pop .= "<tr><td>Lien ressources Com</td><td>".$row_data_event['com_lien']."</td></tr>";

  $str_pop .= "<tr><td colspan='2'></td></tr>";
$str_pop .= "</table>";


 ?>

 <html>
 <head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>TODO EVENT</title>
    <link rel="stylesheet" href="./jquery/css/themes/default/jquery.mobile-1.4.5.min.css">
    <link rel="stylesheet" href="./jquery/_assets/css/jqm-demos.css">

    <script src="./jquery/js/jquery.js"></script>
   <script src="./jquery/_assets/js/index.js"></script>
   <script src="./jquery/js/jquery.mobile-1.4.5.min.js"></script>
   <script>
   var form = new FormData($("#uploadForm")[0]);
 $.ajax({
       url: your_url,
       method: "POST",
       dataType: 'json',
       data: form,
       processData: false,
       contentType: false,
       success: function(result){},
       error: function(er){}
 });
   </script>

 </head>
 <body>

<?=$str_pop?>

 <div data-role="page" class="jqm-demos" data-quicklinks="true">



   <div data-role="header" data-theme="b">
       <h1>Evenementor</h1>
        <a href="./list_items.php" target="blank" class="ui-btn-left ui-btn ui-btn-inline ui-mini ui-corner-all ui-btn-icon-left ui-icon-bars">LISTE DETAIL</a>
        <a href="./list_items_s.php" target="blank" class="ui-btn-right ui-btn ui-btn-inline ui-mini ui-corner-all ui-btn-icon-left ui-icon-bars">LISTE SIMPLE</a>
   </div>

   <div data-role="main" class="ui-content" id="modif">


     <div data-role="collapsible">
         <h4 style="background-color: #a6ff7b;">Heading</h4>
         <p>I'm the collapsible content. By default I'm closed, but you can click the header to open me.</p>
     </div>


     <form action="index2.php" method="post">

          <h2>1. PRIX PUBLIC</h2>
          <input type="range" name="prix_pub" id="slider-3" min="0" max="400" value="" data-show-value="true" data-popup-enabled="true" data-mini="false">
          <h2>2. HEURES MECANO N&Eacute;CESSAIRES</h2>
          <input type="range" name="h_meca" id="slider-3" min="0" max="25" value="" data-show-value="true" data-popup-enabled="true" data-mini="false">
          <div data-role="collapsible">
              <h3 data-icon="arrow-r" >Heading</h3>
            <h4>dsdfs</h4>
              <textarea cols="40" rows="8" name="textarea" id="textarea"></textarea>
          </div>
         <h2>3. MONTANT PI&Egrave;CES & RECYCLAGE</h2>
         <input type="range" name="montant_pieces" id="slider-3" min="10" max="100" value="" data-show-value="true" data-popup-enabled="true" data-mini="false">
         <h2>4. TYPE DE V&Eacute;LO</h2>
         <fieldset data-role="controlgroup" data-type="horizontal" data-mini="true">
           <input type="radio" name="type_velo" id="radio-choice-c" value="1" >
           <label for="radio-choice-c">VTT</label>
           <input type="radio" name="type_velo" id="radio-choice-d" value="2" >
           <label for="radio-choice-d">Vélo ville</label>
           <input type="radio" name="type_velo" id="radio-choice-e" value="3" <>
           <label for="radio-choice-e">Vintage</label>
           <input type="radio" name="type_velo" id="radio-choice-f" value="4" >
           <label for="radio-choice-f">Enfant</label>
         </fieldset>
         <h2>5. TAILLE DE V&Eacute;LO</h2>
         <fieldset data-role="controlgroup" data-type="horizontal" data-mini="true">
           <input type="radio" name="size_velo" id="radio-choice-c-size" value="S" >
           <label for="radio-choice-c-size">S</label>
           <input type="radio" name="size_velo" id="radio-choice-d-size" value="M" >
           <label for="radio-choice-d-size">M</label>
           <input type="radio" name="size_velo" id="radio-choice-e-size" value="L" >
           <label for="radio-choice-e-size">L</label>
           <input type="radio" name="size_velo" id="radio-choice-f-size" value="XL" >
           <label for="radio-choice-f-size">XL</label>
         </fieldset>
         <h2>6. Nom public</h2>
         <input type="text" name="nom_public" id="text-basic" value="">
         <button type="submit" id="submit-1" class="ui-shadow ui-btn ui-corner-all">ENVOYER</button>
     </form>
   </div>
   <div data-role="footer">
   </div>
 </div>
    </body>
  </html>
