<html>
<head>
 <meta charset="utf-8">
 <meta name="viewport" content="width=device-width, initial-scale=1">
 <title>bike pricing</title>
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
</style>
</head>
<body>


   <div data-role="tabs" id="tabs">
     <div data-role="navbar">
       <ul>
         <li><a data-icon="info" style="border-bottom: #a6ff7b thick solid;" href="#one" data-ajax="false">one</a></li>
         <li><a data-icon="info" style="border-bottom: #ffee7b thick solid;" href="#two" data-ajax="false">two</a></li>
         <li><a data-icon="info" style="border-bottom: #ff7b7b thick solid;" href="ajax-content-ignore.html" data-ajax="false">three</a></li>
         <li><a data-icon="info" style="border-bottom: #ff7b7b thick solid;" href="ajax-content-ignore.html" data-ajax="false">three</a></li>
         <li><a data-icon="info" style="border-bottom: #ff7b7b thick solid;" href="ajax-content-ignore.html" data-ajax="false">three</a></li>
       </ul>
     </div>
     <div id="one" class="ui-body-d ui-content">
       <h1>First tab contents</h1>
     </div>
     <div id="two">
       <ul data-role="listview" data-inset="true">
         <li><a data-icon="info" href="#" style="background-color: #a6ff7b;">Données</a></li>
         <li><a data-icon="user" href="#" style="background-color: #ff7b7b;" >Main d'oeuvre</a></li>
         <li><a data-icon="action" href="#" style="background-color: #ff7b7b;" >Régie</a></li>
         <li><a data-icon="shop" href="#"style="background-color: #a6ff7b;" >Restauration</a></li>
         <li><a class="red" data-icon="navigation" href="#" style="background-color: #ffee7b;" >Com</a></li>
       </ul>
     </div>
   </div>

</body>
</html>
