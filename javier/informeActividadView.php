<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);
require_once($_SERVER['DOCUMENT_ROOT'] . '/funciones.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_controllerClass/informeController.php');
$obj = new c_informeController;
$c = $obj->traeDatos();


//c_mysqli::ver($c);

//(
//   [id_e] => Array
//       (
//           [0] => 6
//           [1] => 144
//       )
//
// Ricardo Ramos - Aydee Yazmin
//   [fechaI] => 2020-03-02
//   [fechaF] => 2020-03-02
//)


?>

<!DOCTYPE html>
<html lang="es">
<link href="/datatables/datatables.min.css" rel="stylesheet" type="text/css" />



<script src="/js/jquery-3.3.1.min.js"></script>
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

<!-- verificar si se debe quitar -->
	<script src="/datatables/datatables.min.js"></script>
<script src="/datatables/Buttons-1.5.6/js/dataTables.buttons.min.js"></script>
<script src="/datatables/JSZip-2.5.0/jszip.min.js"></script>
<script src="/datatables/pdfmake-0.1.36/pdfmake.min.js"></script>
<script src="/datatables/pdfmake-0.1.36/vfs_fonts.js"></script>
<script src="/datatables/Buttons-1.5.6/js/buttons.html5.min.js"></script>
<!-- ------------------------------- -->
<script>
<?php
foreach ($c['response_msg']['tabla'] as $k => $d){
?>

   $(document).ready(function() {
      $('#example<?= $k ?>').DataTable({
         language: {
            "lengthMenu": "Mostrar _MENU_ registros",
            "zeroRecords": "No se encontraron resultados",
            "info": "Mostrando registros del _START_ al _END_ de un total de _TOTAL_ registros",
            "infoEmpty": "Mostrando registros del 0 al 0 de un total de 0 registros",
            "infoFiltered": "(filtrado de un total de _MAX_ registros)",
            "sSearch": "Buscar:",
            "oPaginate": {
               "sFirst": "Primero",
               "sLast": "Último",
               "sNext": "Siguiente",
               "sPrevious": "Anterior"
            },
            "sProcessing": "Procesando...",
         },
         "aLengthMenu": [[5, 10, 15, 25, 50, 100 , -1], [5, 10, 15, 25, 50, 100, "All"]], "iDisplayLength" : 100,
         //para usar los botones   
         responsive: "true",
         dom: 'Bfrtilp',
         buttons: [{
               extend: 'excelHtml5',
               text: '<i class="far fa-file-excel" ></i> ',
               titleAttr: 'Exportar a Excel',
               className: 'btn btn-success mr-3 col-sm-1 col-md-4 rounded-circle'
            },
            {
               extend: 'pdfHtml5',
               text: '<i class="fas fa-file-pdf" ></i> ',
               titleAttr: 'Exportar a PDF',
               className: 'btn btn-danger mr-3 col-sm-1 col-md-4 rounded-circle'
            },
            {
               extend: 'print',
               text: '<i class="fa fa-print "></i> ',
               titleAttr: 'Imprimir',
               className: 'btn btn-info col-sm-1 col-md-4 rounded-circle'
            },
         ]
      });
   });
<?php
}
?>

</script>






<script src="/fontawasome-ico.js"></script>



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
                     <select class="form-control" name="id_e[]" multiple size="7">
                        <?php
                        foreach ($c['users'] as  $i  => $d) {
                           $sel = (isset($_POST['id_e']) && in_array($i, $_POST['id_e'])) ? ' selected' : '';
                           echo '<option' . $sel . ' value="' . $i . '">' . $d . '</option>';
                        }
                        ?>
                     </select>
                  </div>
                  Fecha inicial
                  <div class="form-group"><input class="form-control" name="fechaI" value="<?= $_POST['fechaI'] ?? date('Y/m/d')  ?>" type="date">
                  </div>
                  Fecha final
                  <div class="form-group"><input class="form-control" name="fechaF" value="<?= $_POST['fechaF'] ?? date('m/d/Y')  ?>" type="date">
                  </div>
                  <input class="btn btn-success btn-block" type="submit" value="Buscar usuario">
               </form>
            </card>
         </div>
      </div>
   </div>

   <div class="d-flex container-fluid">
      <div class="mr-2 col-md-12">

         <?php
         if ($c['response_status'] == 'ok') {
            foreach ($c['response_msg']['tabla'] as $k => $d) {
               $tmp = explode(' ', $c['users'][$k]);
               $nom = ($tmp[0] ?? '') . ' ' . ($tmp[1] ?? '') . ' ' . ($tmp[2] ?? '');
         ?>

               <div class="caja-Prod row ">
                  <div class=" col-md-2 my-3">
                     <div class="mr-2 ">
                        <b>OPERARIO:</b> <span class="tex-center mx-auto badge badge-table badge-secondary ml-2"><?= $nom   ?></span>
                        <div class="row my-2">
                           <div class="col-md-6">
                              <img src="https://bysperfeccionoral.com/wp-content/uploads/2020/01/136-1366211_group-of-10-guys-login-user-icon-png.jpg" alt="" height="90" width="120px">
                           </div>
                           <div class="col-md-6">
                              <img src="https://img2.freepng.es/20180410/wiq/kisspng-computer-icons-chart-symbol-clock-5acd7f2b756030.1399734315234168754808.jpg" alt="" height="90" width="120px">
                           </div>
                        </div>
                        <!-- 
                           <img src="/_clientes/ittpruebas/fotos/10.jpg" alt="" class="img-fluid rounded p-1" width="120px">
                            -->
                     </div>
                  </div>
                  <div class="table-responsive my-3 col-md-10">
                     <table id="example<?= $k?>">
                        <thead>
                           <tr>
                              <th width="100px">OP</th>
                              <th width="600px">Tipo de trabajo</th>
                              <th width="300px">Referencia</th>
                              <th width="300px">Referencia D.T.</th>
                              
                              <th width="200px">Cantidad</th>
                              <th width="200px">Hora Inicia</th>
                              <th width="200px">Hora Termina</th>
                              <th width="200px">Total Tiempo</th>
                           </tr>
                        </thead>
                        <tbody>
                           <?php
                           foreach ($d as $i => $row) {
                           ?>
                              <tr>
                                 <td> <?= (($row[3]) ? '<a href="">' . $row[3] . '</a>' : '')  ?> </td>
                                 <td><?= $row[2] . $row[6] ?></td>
                                 <td><?= $row[4] ?></td>
                                 <td><?= $row[9] ?></td>
                                 <td><?= $row[8] ?></td>
                                 <td><?= $row[0] ?></td>
                                 <td><?= $row[1] ?></td>
                                 <td><?= $row[5] ?></td>
                              </tr>
                           <?php
                           }
                           ?>
                        </tbody>
                        <tr>
                           <?php if (isset($c['response_msg']['total'][$k]['i'])) {   ?>
                              <td colspan="7" class="verde-manzana">
                                 <h6 class="verde-manzana text-center">TIEMPO IMPRODUCTIVO</h6>
                              </td>
                              <td class="verde-manzana">
                                 <h6 class="verde-manzana text-center "><?= $c['response_msg']['total'][$k]['i'] ?></h6>
                                 </h6>
                              </td>
                           <?php } ?>
                        </tr>
                        <tr>
                           <?php if (isset($c['response_msg']['total'][$k]['p'])) {   ?>
                              <td colspan="7" class="verde-manzana">
                                 <h6 class="verde-manzana text-center">TIEMPO PRODUCTIVO</h6>
                              </td>
                              <td class="verde-manzana">
                                 <h6 class="verde-manzana text-center "><?= $c['response_msg']['total'][$k]['p'] ?></h6>
                                 </h6>
                              </td>
                           <?php } ?>
                        </tr>
                        <tr>
                           <?php if (isset($c['response_msg']['total'][$k]['j'])) {   ?>
                              <td colspan="7" class="verde-manzana">
                                 <h6 class="verde-manzana text-center">TIEMPO JORNADA</h6>
                              </td>
                              <td class="verde-manzana">
                                 <h6 class="verde-manzana text-center "><?= $c['response_msg']['total'][$k]['j'] ?></h6>
                                 </h6>
                              </td>
                           <?php } ?>
                        </tr>

                     </table>
                  </div>
                  <!-- Grafica ---------------------------------------------------------------->
                  <div class="container my-4">
                     <div id="chrt<?= $k ?>" width="768" style="height: <?= ($totItem) ?>px"></div>
                  </div>
                  <!-- ------------------------------------------------------------------------- -->
               </div>
         <?php
            }
         } else {
            if ($c['response_msg'] != '') {
               echo '<script>alert("' . $c['response_msg'] . '");</script>';
            }
         }

         ?>
      </div>
   </div>
   </div>

   <script>
      <?php
      foreach ($c['response_msg']['tabla'] as $k => $d) {
      ?>
         google.charts.load('current', {
            'packages': ['timeline']
         });
         google.charts.setOnLoadCallback(drawChart<?= $k ?>);

         function drawChart<?= $k ?>() {
            var data = new google.visualization.DataTable();
            data.addColumn({
               type: 'string',
               id: 'Tipo'
            });
            data.addColumn({
               type: 'string',
               id: 'Tipo'
            });
            data.addColumn({
               type: 'date',
               id: 'Start'
            });
            data.addColumn({
               type: 'date',
               id: 'End'
            });
            data.addRows([
               <?php
               $tipo = ['j' => 'Jornada', 'p' => 'Productivo', 'i' => 'Improductivo'];
               foreach ($d as $row) {
                  echo '["' . strip_tags($row[2]) . ' ' . $row[4] . $row[6] . '","' . $tipo[$row[7]] . '", new Date(' . convierteFecha($row[0]) . '), new Date(' . convierteFecha($row[1]) . ')],' . PHP_EOL;
               }
               ?>
            ]);
            var options = {
               'title': 'Reporte por operario',
               hAxis: {
                  title: 'Item'
               },
               vAxis: {
                  title: 'Periodo'
               },
               colors: <?= $c['response_msg']['css'][$k] ?>,
               height: 450,
               gantt: {
                  trackHeight: 500
               },

            };
            var chart = new google.visualization.Timeline(document.getElementById('chrt<?= $k ?>'));
            chart.draw(data, options);
         }
      <?php
      }
      ?>
   </script>

</html>

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

   $('table').addClass('table table-bordered table-striped table-sm table-hover');
   $('table thead tr').addClass('azuldark text-white text-center p-2');
   $('table tbody td').addClass('px-1 align-middle');
   $('table tbody td:nth-child(n+4)').addClass('text-center');
   $('table tbody td:nth-child(1)').addClass('text-center');
   $('table tbody td:nth-child(1) a').addClass('badge-ineditto badge-grey badge-table');
</script>
<script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

</html>

<?php
c_mysqli::ver($c);
?>