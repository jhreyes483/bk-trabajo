<?php
//============================================================
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);
//require_once('_modeloClass/factModelo.php');
require_once('funciones.php');
require_once('_controllerClass/class.informe.php');
$objMod  = new c_reportes;
$objCon = new c_Informe; // Objeto del controler
/// 3 5 25 24 27 43  67 76 80 85 88 89 90 94 96 97 
//=========================================================
// CAPTURA DATOS
$id   = $_POST['selectId'];
$fIni = $_POST['fIni'];
$fFin = $_POST['fFin'];
// 3 13
if (isset($id)) $datosT = $objCon->TraeTodosLosDatos(implode(',', $id), $fIni, $fFin);

$totReg  = count($datosT);
$salida  = false;
$entrada = false;
//crea array porque no finaliza la jornada
if (isset($datosT)) {
   foreach ($datosT as $i => $d) {
      if ($datosT[$i][2] == 'e') {
         $entrada = true;
         //  echo '<script>alert("entrada cambio a true");</script>';  
      }
      if ($datosT[$i][2] == 's') {
         $salida = true;
      }
   }

   foreach ($datosT as $i => $d) {
      if ($datosT[$i][2] == 'e') {
         if ($entrada == true && $salida == false) {
            // Validacion de actividad
            // Crea arreglo de salida para que se muestre en la grafica
            $datosT[] = [$datosT[$i][0], (substr($datosT[$i][1], 0, 10) . ' 23:59:59'), 's', 1, $datosT[$i][4]];
            echo '<script>alert("Usuario no finalizo jornada en la fecha actual");</script>';
         }
      }
   }
   $salida  = false;
   $entrada = false;

   //=======================================================
   // Elimina registro de doble salida "error marcacion usuario"
   foreach ($datosT as $i => $d) {
      if ($datosT[$i][2] == 's' && $datosT[$i][3] == 1) {
         if (isset($datosT[$i + 1][2]) &&  $datosT[$i][2] == $datosT[$i + 1][2]) {
            unset($datosT[$i + 1]);
         }
      }
   }

   // Elimina registro de doble entrada "error marcacion usuario"
   if (isset($datosT)) {
      foreach ($datosT as $i => $d) {
         if (
            isset($datosT[$i + 1][2]) &&  $datosT[$i][2] == 'e' && $datosT[$i + 1][2] == 'e' &&
            $datosT[$i][3] == '1' && $datosT[$i + 1][3] == '1'
         ) {
            unset($datosT[$i + 1]);
         }
      }
   }

   //=======================================================
   foreach ($datosT as $i => $d) {

      if ($d[2] == 'e' && $d[3] == 1) $ij = $d[1];
      if ($d[2] == 's' && $d[3] == 1) $jornada[$i] = [$d[4], $ij, $d[1], $d[0], '', '', $d[0]];
      if ($d[2] == 'e' && $d[3] == 2) $i1 = $d[1];
      if ($d[2] == 's' && $d[3] == 2)  $otros[$i] = [$d[4], $i1, $d[1], "", 1, $d[0]];
      if ($d[2] == 's' && $d[3] == 7) $hExtra[$i] = [$d[4], $ij, $d[1], $d[0]];
   }





   //=======================================================
}

// Usuario no marco entrada, se crea array para generar salida de manera correcta
if (count($jornada) > 0) {
   foreach ($jornada as $i => $d) {
      if (
         isset($datosT[$i + 1][2]) && isset($jornada[$i + 1]) &&  isset($jornada[$i + 3]) &&
         $datosT[$i][2]   == 's' &&
         $datosT[$i + 1][2] == 's' &&
         $datosT[$i][3]   == $datosT[$i + 1][3]
      ) {
         $fecha           = substr($jornada[$i + 1][2], 0, 10);
         $hora            = substr($datosT[$i][1], -9);
         $temp[]          = $jornada[$i];
         $temp2[]         = [$jornada[$i][0], $jornada[$i + 1][2], $fecha . $hora];
         $jornada[$i]     = $temp[0];
         $jornada[$i + 1]   = $temp2[0];
         unset($temp, $temp2);
      }
   }


   //======================================================
   // crea array temporal y lo suscribe con fechas correctas
   foreach ($jornada as $i => $d) {
      if ($jornada[$i][1] == '') {
         $temp[$i] = $jornada[$i];
         $temp2[$i] = $jornada[$i + 2];
         $jornada[$i + 2] = [$temp[0][0], $temp[0][2], $temp2[0][2]];
         unset($jornada[$i]);
      }
   }

   //
   if (isset($datosT)) {
      foreach ($datosT as $i => $d) {
         if (isset($jornada[$i + 2][2]) && substr($jornada[$i][2], 0, 10) ==  substr($jornada[$i + 2][2], 0, 10)) {
            unset($jornada[$i + 2]);
         }
      }
   }
}

//======================================================
// Se crea nuevo arreglo  con inicios y finales por labor prod. e improductivos
$actividad = false;
if (isset($datosT)) {
   foreach ($datosT as $k => $d) {
      if ($d[2] == 'I' || $d[2] == 'R') $tIni = $d[1];
      $actv = $d[4];
      if ($d[2] == 'F' && $d[4] == $actv  || $d[2] == 'P'
         //&& $d[4] == $op 
      ) {
         // $tFin = $datosT[$i][1]; 
         $tFin = $d[1];
         $actividad = true;
      } else {
         $tFin = '';
      }
      if ($actividad == false &&  $datosT[$k + 1][6] > 1000 || $d[6] > 1000)
         $tFin = $datosT[$k + 1][1];
      $p = ($datosT[$k][4] == '') ? 1 : 2; // marca el tiempo sin labor  y labor para posteriormete sumar los valores
      if ($tFin != '')
         $arrTiem[$k] = [$d[4] ?? strtoupper($d[7]), $tIni, $tFin, $datosT[$k][3], $p, $d[0]];
   }
}



//==========================================
// Asignacion de largo div de grafica
$totTiem = count($arrTiem);
$totItem = ($totReg + $totTiem) * 7.5;
echo $totItem . '<br>';

if ($totItem > 500) {
   echo 'v1';
   $totItem = $totItem / 1.5;
   if ($totItem > 1500) {
      echo 'v2';
      $totItem = $totItem / 1.74;
      if ($totItem > 1200 && $totItem < 6000) {
         echo 'v3';
         $totItem = $totItem * 1.2;
         if ($totItem > 2500) {
            echo 'v4';
            $totItem = $totItem * 1.055;
            if ($totItem < 6200) {
               echo 'v5';
               $totItem = $totItem / 0.8705;
            }
         }
      }
      if ($totItem > 4500) {
         $totItem = $totItem / 1.7;
         if ($totItem > 6000) {

            $totItem = $totItem * 1.35;
         }
      }
      if ($totItem < 5000) {
         if ($totItem > 3000) {
            $totItem = $totItem * 1.5;
         }
         $totItem = $totItem / 1.09;
         if ($totItem > 1820 && $totItem < 2245) {
            $totItem = $totItem * 1.1;
         }
      }
   }
   if ($totItem < 500) {
      $totItem = $totItem * 1.4;
   }
} else {
   $totItem = $totItem;
   if ($totItem < 500) {
      $totItem = $totItem * 2;
   }
   if ($totItem > 500) {
      $totItem = $totItem / 1.45;
   }
}

//======================================================
// CATCH errores array arrTiemp
//======================================================
if (isset($arrTiem)) {
   foreach ($arrTiem as $i => $d) {
      if ($arrTiem[$i][3] == 1) unset($arrTiem[$i]);
   }
}

//========================================================
// CATCH errores de todos los arrays 
$arrTiem  =  $objCon->verificaArray($arrTiem);
$jornada  =  $objCon->verificaArray($jornada);
$otros    =  $objCon->verificaArray($otros);
$hExtra   =  $objCon->verificaArray($hExtra);
c_MySQLi::ver($jornada);
//=======================================================

//Suma array y valida que sean mayor a 0
$arrTotal = [];

if (count($jornada) > 0) $arrTotal = $jornada;

if (count($hExtra) > 0) $arrTotal = $arrTotal + $hExtra;
if (count($otros) > 0) $arrTotal = $arrTotal + $otros;
if (count($arrTiem) > 0) $arrTotal = $arrTotal + $arrTiem;
$arrTotal       =  $objCon->verificaArray($arrTotal);
// Operaciones del modulo
//=================================================================
$arrTotal        =  $objCon->calcularTiempoActividad($arrTotal); // Retorna el tiempo de actividad en segundos

$tiemSinLabor    =  $objCon->sumaTiempoConFiltro($arrTotal, 1); // Retorna tiempo improductivo en segundos
$tiemProductivo  =  $objCon->sumaTiempoConFiltro($arrTotal, 2); // Retorna tiempo productivo en segundos

//=================================================================

// Formateo de datos
//====================================================================
//Se crea array de tabla con formato de hora humain_time

if (isset($arrTotal)) {
   foreach ($arrTotal as $i => $d) {
      $referencia   =  ($datosT[$i][3] == '' ? $datosT[$i][4] : '');
      $tTrabajo     =  ($datosT[$i][9] != '' ? $datosT[$i][9] : $datosT[$i][4]);
      $tTrabajo     =  ($datosT[$i][7] != '' ? $datosT[$i][7] : $tTrabajo);
      $arrTotal[$i] =
         [
            ($datosT[$i][8] ?? 'N/A'), $tTrabajo, $referencia,
            $arrTotal[$i][1], $arrTotal[$i][2], human_time($arrTotal[$i][3], 0, 1, 1), $d[5]
         ];
   }
}


// Si no existe jornada por que no ha finalzado labor 

// if ((count($jornada) < 0) && (count($arrTotal) > 0) || (!isset($jornada)) && (count($arrTotal) > 0)) {
//$last = array_key_last($arrTotal);

//  foreach($datosT  as $i => $d ) if( $i ){  $lastFecha  = $d[1]; } 
//     $jorn[]  = ['N/A', 'NO REGISTRO JORNADA', '', $datosT[0][1], $datosT[0][1], 'No registro jornada',$datosT[0][0]];
// Suma a array total
//  $arrTotal  =  array_merge($arrTotal, $jorn);
//  $arrTotal  =  array_reverse($arrTotal);
// }


$arrTotal = $objCon->verificaArray2($arrTotal);
c_MySQLi::ver($arrTotal); 
if (isset($arrTotal)) foreach ($arrTotal as $t) $tmp[$t[6]][] = $t;


//=====================================================================
$users = $objCon->traeEmpleado()
//=====================================================================
// HTML
?>
<!DOCTYPE html>
<html lang="es">
<script src="/JsScripts/jquery-1.9.1.js"></script>
<script src="/JsScripts/Chart_2_9_3/Chartmin.js"></script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>
<script src="/js/fontawesome-all.js"></script>
<SCRIPT src="/archivosMenu/JSCookMenu.js" type=text/javascript> </SCRIPT> <SCRIPT src="/archivosMenu/theme.js" type=text/javascript> </SCRIPT> <head>
   <meta charset="iso-8859-1">
   <meta name="viewport" content="width=device-width, initial-scale=1.0">
   <meta http-equiv="pragma" content="no-cache">
   <meta http-equiv="Content-Type" content="text/html; charset=ISO-8859-1"/>
   <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" integrity="sha384-JcKb8q3iqJ61gNV9KGb8thSsNjpSL0n8PARn9HuZOnIxN0hoP+VmmDGMN5t9UJ0Z" crossorigin="anonymous">
   <title>Reporte operario</title>
   <link href="/css/estilos.css" rel="stylesheet" type="text/css">
   <link href="/css/font-awesome.min.css" rel="stylesheet" type="text/css">
   <link rel="stylesheet" type="text/css" href="/bootstrap/bootstrap.min.css"/>
   <link href="/archivosMenu/theme.css" rel="stylesheet" type="text/css">
   <link rel="stylesheet" type="text/css" href="/_clientes/ittpruebas/estilo.css"/>
   <link href="/css/ineditto.css" rel="stylesheet" type="text/css">
</head>
<body>


<div class="container-fluid ">
      <div class="container-pt-4 my-4">
         <div class="card card-body col-md-4 col-lg-8  text-center mx-auto bk-rgb shadow p-3 mb-5 bg-white ">
            <h4 class="">Reporte de operario</h4>
         </div>
      </div><br><br>
      <div class="row ">
         <div class="col-lg-3  col-sm-4 col-md-5 mx-auto my-4  ">
            <card class="shadow-lg card card-body mx-auto">
               <form method="POST">
               <h6 class="card-header shadow my-2">Formualrio de busqueda</h6>
                  Operario
                  <div class="form-group">
                     <select class="form-control" name="selectId[]" multiple size="7">
<?php
foreach ($users  as $i) {
   $sel = (isset($_POST['selectId']) && in_array($i[0], $_POST['selectId'])) ? ' selected' : '';
   echo '<option' . $sel . ' value="' . $i[0] . '">' . $i[1]  . '</option>';
}
?>  
                     </select>
                  </div>
                  Fecha inicial
                  <div class="form-group"><input class="form-control" name="fIni" value="<?= $fIni ?? date('Y/m/d')  ?>" type="date">
                  </div>
                  Fecha final
                  <div class="form-group"><input class="form-control" name="fFin" value="<?= $fFin ?? date('m/d/Y')  ?>" type="date">
                  </div>
                  <input class="btn btn-success btn-block" type="submit" value="Buscar usuario">
               </form>
            </card>
         </div>
   </div>
</div>

      <div class="d-flex container-fluid">
         <div class="mr-2 col-md-12">
         <div class="caja-Prod row ">
<?php

if (isset($tmp)) foreach ($tmp as $k => $d) {
?>

                     <div class=" col-md-2 my-3">
                        <div class="mr-2 ">
                           <b>OPERARIO:</b> <span class="tex-center mx-auto badge badge-table badge-secondary ml-2"><?= $k ?></span>
                           <div class="row my-2">
                              <div class="col-md-6">
                              <img src="https://bysperfeccionoral.com/wp-content/uploads/2020/01/136-1366211_group-of-10-guys-login-user-icon-png.jpg" alt="" height="90"  width="120px">
                              </div>
                              <div class="col-md-6">
                              <img src="https://img2.freepng.es/20180410/wiq/kisspng-computer-icons-chart-symbol-clock-5acd7f2b756030.1399734315234168754808.jpg" alt="" height="90"  width="120px">
                              </div>
                           </div>
                           <!-- 
                           <img src="/_clientes/ittpruebas/fotos/10.jpg" alt="" class="img-fluid rounded p-1" width="120px">
                            -->
                        </div>  
                     </div>
                     <div class="table-responsive my-3 col-md-10">
                        <table>
                           <thead >
                              <tr >
                                 <th width="100px" >OP</th>
                                 <th width="150px">Tipo de Trabajo</th>
                                 <th width="500px">Referencia</th>
                                 <th width="200px">Hora Inicia</th>
                                 <th width="200px">Hora Termina</th>
                                 <th  width="200px">Total Tiempo</th>
                              </tr>
                           </thead>
                           <tbody>
<?php
   foreach ($d as $i => $row) {
?>

                              <tr>
                                 <td ><a href=""><?= $row[0] ?></a> </td>
                                 <td ><?= $row[1] ?></td>
                                 <td ><?= $row[2] ?></td>
                                 <td ><?= $row[3] ?></td>
                                 <td ><?= $row[4] ?></td>
                                 <td > <?= $row[5] ?></td>
                              </tr>
<?php
   }
?>
<tr>
   <?php if ($tiemSinLabor[$k] > 0) {   ?>   
   <td colspan="5" class="verde-manzana"><h6 class="verde-manzana text-center">TIEMPO IMPRODUCTIVO</h6 ></td>
   <td class="verde-manzana" > <h6  class="verde-manzana text-center " ><?= human_time($tiemSinLabor[$k], 0, 1, 1); ?></h6></h6></td>
   <?php } ?>
   </tr>
   <tr>
   <?php if ($tiemProductivo[$k] > 0) {   ?>   
   <td colspan="5" class="verde-manzana"><h6 class="verde-manzana text-center">TIEMPO PRODUCTIVO</h6 ></td>
   <td class="verde-manzana" > <h6  class="verde-manzana text-center " ><?= human_time($tiemProductivo[$k], 0, 1, 1); ?></h6></h6></td>
   <?php } ?>
</tr>

                        </tbody>
                     </table>
                  </div>
<?php
}
?>
     </div>
   </div>
</div>     

<?php
if (isset($id)) {
   if (count($datosT) > 1) {
?>


<!-- Grafica ---------------------------------------------------------------->
<div class="container my-4">
   <div class="row">
      <div class="col-md-12">
         <div class="caja-Prod shadow-lg"></div>
         <div class="box-body ">
            <div class="d-flex"></div>
            <div id="chrt" width="768" style="height: <?= ($totItem) ?>px"></div>
         </div>
      </div>
   </div>
</div>
<!---------------------------------------------------------------------------->


<script>
   google.charts.load( 'current', {
      'packages': [ 'timeline' ]
   } );
   google.charts.setOnLoadCallback( drawChart );
   function drawChart() {
      var container = document.getElementById( 'chrt' );
      var chart = new google.visualization.Timeline( container );
      var data = new google.visualization.DataTable();
      data.addColumn( { type: 'string',id: 'Tipo' } );
      data.addColumn( { type: 'date'  ,id: 'Start'} );
      data.addColumn( { type: 'date'  ,id: 'End'  } );
      data.addRows( [
   <?php

      if (count($arrTotal) != 0) {
         foreach ($arrTotal as $i => $d) {
            $activ = ($arrTotal[$i][2] != '') ? $arrTotal[$i][2] :  $arrTotal[$i][1]; //Organiza los datos para la grafica
            echo '["' . $activ . '", new Date(' . convierteFecha($d[3]) . '), new Date(' .   convierteFecha($d[4]) . ')],' . PHP_EOL;
         }
      }
   ?>
         ] );
         var options = { height: <?= ($totItem) ?>,gantt: {trackHeight: <?= ($totItem) ?>}};
         chart.draw( data );
      }
</script>

<?php
   } else {
      echo "<script>alert('No hay actividad en fecha solicitada');</script>";
   }
}
?>







<script>
   $("input").addClass("form-control ")
   $("label").addClass("col-form-label")
   $("select").addClass("form-control ")
   $("#reporte").addClass("table table-sm table-bordered table-striped")
   $("#reporte td").addClass("px-1 align-middle")
   $("#reporte thead th").addClass("azuldark text-white text-center")
   $("#reporte tbody td:nth-child(1)").addClass("text-center")
   $("#reporte tbody td:nth-child(n+4)").addClass("text-center")
   $("#reporte tfoot td").addClass("bg-info text-center text-white f12 font-weight-bold")
</script>


























</body>
<script src="/js/bootstrap.min.js"></script>
<script type="text/javascript" src="/JsScripts/google/loader.js"></script>

<script>
   $('table').addClass('table table-bordered table-striped table-sm');
   $('table thead tr').addClass('azuldark text-white text-center p-2');
   $('table tbody td').addClass('px-1 align-middle');
   $('table tbody td:nth-child(n+4)').addClass('text-center');
   $('table tbody td:nth-child(1)').addClass('text-center');
   $('table tbody td:nth-child(1) a').addClass('badge-ineditto badge-grey badge-table');
</script>

</html>