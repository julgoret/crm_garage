<?php

include_once('./connect_inc.php');

$req ="SELECT * FROM `prospects` WHERE `id`='".$_GET['id_ev']."'";
$res  = mysqli_query($link, $req);
$row  = mysqli_fetch_assoc($res);


$req_data_event_operateur ="SELECT * FROM `operateurs`";
$res_data_event_operateur  = mysqli_query($link, $req_data_event_operateur);
while($row_data_event_operateur  = mysqli_fetch_assoc($res_data_event_operateur))
{
  if($row_data_event_operateur['id'] == $row['fkid_operateur'])
  {
    $selected_str_prep = $row_data_event_operateur['name'];
  }

  if($row_data_event_operateur['id'] == $row['fkid_operateur_dday'])
  {
    $selected_str_dday = $row_data_event_operateur['name'];
  }
}

$req_type ="SELECT * FROM `types`";
$res_type  = mysqli_query($link, $req_type);
while($row_type  = mysqli_fetch_assoc($res_type))
{
  if($row_type['id'] == $row['fkid_type'])
  {
    $str_type = $row_type['name'];
  }

}

 ?>
<html>
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
    table{

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
    textarea{
      width:300px;

    }
    input{
      width:300px;
    }
    select{
      width:300px;
    }
    td{
      padding: 5px;
    }
  </style>
  </head>
  <body>

    <h1><?=$row['title_event']?></h1>
    <h2><?=$str_type?></h2>
    <table border='1'><tr><td class='tabcol' colspan='2'><h3>DONN&Eacute;S événement</h3></td>
    </tr>
    <tr><td>Date début accueil public</td>
    <td><?=date("d/m/Y", $row['time_beg'])?></td>
    </tr>
    <tr><td>Date fin accueil public</td>
    <td><?=date("d/m/Y", $row['time_end'])?></td>
    </tr>
    <tr><td class='tabcol_list'> Horaires : </td>
    <td><?=nl2br($row['horaires'])?></td>
    </tr>
    <tr><td class='tabcol_list'> Horaires techniques : </td>
    <td><?=nl2br($row['horaire_technique'])?></td>
    </tr>
    <tr><td>Organisateur (Structure + référent)</td>
    <td><?=nl2br($row['name'])?></td>
    </tr>
    <tr><td>Tel</td>
    <td><?=nl2br($row['tel'])?></td>
    </tr>
    <tr><td>Mail</td>
    <td><?=nl2br($row['mail'])?></td>
    </tr>
    <tr><td>Lien dossier</td>
    <td><?=nl2br($row['link_devis'])?></td>
    </tr>
    <tr><td>Déroulé </td>
    <td><?=nl2br($row['comment_deroule'])?></td>
    </tr></table><table border='1'><tr><td  class='tabcol'  colspan='2'><h3>MAIN D'OEUVRE</h3></td>
    </tr>
    <tr><td class='tabcol_list'>Référent général</td>
    <td><?=$selected_str_prep?></td>
    </tr>
    <tr><td>Référent Jour J</td>
    <td><?=$selected_str_dday?></td>
    </tr>
    <tr><td>Main d'oeuvre</td>
    <td><?=nl2br($row['mo_comm'])?></td>
    </tr></table><table border='1' ><tr><td class='tabcol' colspan='2'><h3>R&Eacute;GIE / ESPACES</h3></td>
    </tr>
    <tr><td class='tabcol_list'>Jauge estimée</td>
    <td><?=nl2br($row['jauge_ttl'])?></td>
    </tr>
    <tr><td>Régie</td>
    <td><?=nl2br($row['espaces'])?></td>
    </tr></table><table border='1'><tr><td class='tabcol' colspan='2'><h3>RESTAURATION</h3></td>
    </tr>
    <tr><td class='tabcol_list'>Besoin resto public*</td>
    <td><?=nl2br($row['comment_resto_public'])?></td>
    </tr>
    <tr><td>Besoin resto catering*</td>
    <td><?=nl2br($row['comment_resto_cater'])?></td>
    </tr>
    <tr><td>Courses spécifiques*</td>
    <td><?=nl2br($row['liste_courses'])?></td>
    </tr>
    <tr><td>Moyens paiement</td>
    <td><?=nl2br($row['moyens_pay'])?></td>
    </tr></table><table border='1'><table border='1'><tr><td class='tabcol' colspan='2'><h3>COMMUNICATION</h3></td>
    </tr>
    <tr><td>Remarques / Contact Com</td>
    <td><?=nl2br($row['com_comm'])?></td>
    </tr>
    <tr><td>Comptes RS à mentionner</td>
    <td><?=nl2br($row['comptes_rs'])?></td>
    </tr>
    <tr><td>Lien ressources Com</td>
    <td><?=nl2br($row['com_lien'])?></td>
    </tr></table><table border='1' ><tr><td colspan='2'><h3>CONTENUS COM SITE WEB</h3></td>
    </tr>
    <tr><td class='tabcol_list'><span>Sous-titre PUBLIC</span></td>
    <td><?=nl2br($row['subtitle_public'])?></td>
    </tr>
    <tr><td class='tabcol_list'><span>lien vers event PUBLIC </span></td>
    <td><?=nl2br($row['link_event'])?></td>
    </tr>
    <tr><td><span>Desc PUBLIC</span></td>
    <td><?=nl2br($row['desc_gene_public'])?></td>
    </tr>
    <tr><td><span>Programme</span></td>
    <td><?=nl2br($row['programme'])?></td>
    </tr></div></div>


    </body>
  </html>
