<?php
require_once('_controllerClass/contabilidadadCtl.class.php');
echo 'vista';

$obj = new c_Contabilidad();
$aC = $obj->traeDatos();
c_MySQLi::ver($aC);


//
echo 'here';

?>


<!DOCTYPE html>
<html lang="es">

<head>
   <meta charset="iso-8859-1">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
   <title>Document</title>
   <script src="/JsScripts/jquery-1.9.1.js"></script>
</head>


<body>

<form action="" method="post">
   <div class="container-fluid">
      <div class="row">
         <div class="card card-body shadow  col-md-2 mx-auto">
            <label for="">Proveedores</label>
            <select name="users" id=""  onchange="submit(this)">
<?php
foreach($aC['proveedores'] as $i => $d ) echo '<option'.(($i == $_POST['users'] )? ' selected ' :''  ).' value="'.$i.'">'.$d.'</option>';       
?>
            </select>
         </div>
      </div>
   </div>
   </form>


</body>

<script>
   $('select').addClass('form-control');
</script>