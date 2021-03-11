<?php

/*****************************

 *******************************/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);
require_once($_SERVER['DOCUMENT_ROOT'] . '/funciones.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/_modeloClass/informeModel.php');

// tabla de la que 
// echo 'controller'; die();
class  c_informeController
{
   private $odbc;
   public function __construct(){
      $this->odbc = new c_informeModel;
   }
   public function traeDatos(){
      $r = [];
      if (isset($_POST) &&  !empty($_POST)) {
         extract($_POST);
         $dato[1] = (count($id_e) > 0          ? ' AND AV.id_empleado IN (' . implode(',', $id_e) . ')' : '');
         $dato[2] = ($fechaI != '')            ? ' AND AV.hora BETWEEN "' . $fechaI . '" AND "' . $fechaF . ' 23:59:59"' : '';
         $dato[3] = (isset($op) && $op != '')  ? ' AND O.nOrden = ' . $op : '';
         $resul       = $this->odbc->consPeriodo($dato); // trae todos los items
         if ($resul->num_rows != 0) {
            foreach ($resul->rows as $r) {
               $this->id_users[$r[8]] = $r[8];
               $this->id_users = array_unique($this->id_users);
               $bloques[$r[0]][] = array($r[1], $r[9], $r[3], $r[4], $r[5], $r[0], 20 => $r[8], 23 => $r[2],27=>$r[10], 28=>$r[11], 30 => 'p'); // $r[2]
            }
            // Captura jornada
            if ($this->id_users) {
               $dato[0] =  'id_empleado ,tipo, LEFT(fecha ,16) as fecha';
               $dato[1] =  implode(',', $this->id_users);
               $dato[2] = ' AND fecha BETWEEN "' . $fechaI . '" AND "' . $fechaF . '  23:59:59"';
               $dato[3] = 'ORDER BY id_empleado, fecha';
               $dato[4] = '';
               $jornada =   $this->odbc->consMarcacionEmp($dato); // consulta de jornada
               $r = $this->verifResut($jornada->rows);
               if ($r['response_status'] == 'ok') {
                  foreach ($r['response_msg']  as $i => $d) {
                     if ($d[1] == 'e' && $r['response_msg'][$i + 1][1] == 's') {
                        $in =  $d[2];
                        $off = $r['response_msg'][$i + 1][2];
                        $this->jornada[] = [$in, $off, 'Jornada', '', '', 20 => $d[0], 25 => 'Jornada', 22 => 'Jornada', 30 => 'j'];
                     }
                  }
               }
            }

            //Si hay un grupo impar, agrego otro elemento para finalizar lo iniciado
            foreach ($bloques as $k => $b) {
               $totItems = count($b);
               $impar = ($totItems % 2 == 1 ? true : false);
               if ($impar && $k > 0) {
                  // Caso de no finalizar op 
                  // fecha inicial ($b[0][2] id usuario $b[0][20];
                  $f = $this->capturaFin($b[0][20], $b[0][2] );
                  
                  $bloques[$k][] = array($b[0][0], $b[0][1], $f, 'F', $b[0][4], $b[0][5], '*INSERTADO', 20 => $b[0][20], 23 => $b[0][23],  30 => 'p');
               } else if ($k == 0) { //Verifico las marcaciones de tiempo improductivo, pueden ser pares
                  $tot = count($b);
                  if ($impar) $tot++;
                  for ($i = 0; $i <= $tot; $i++) { //Agrego Horas de finalización
                     $tmp = array(array($bloques[$k][$i][0], $bloques[$k][$i][1], $bloques[$k][$i][2], 'F', $bloques[$k][$i][4], $bloques[$k][$i][5], '*INSERTADO IMP*', 20 => $bloques[$k][$i][20], 30 => $bloques[$k][$i][30]));
                     array_splice($bloques[$k], ($i + 1), 0, $tmp);
                     $i++;
                  }
                  unset($tmp);
               }
            }
            foreach ($bloques as $k => $b) {
               foreach ($b as $i => $n) {
                  if ($n[3] == 'I') {
                     $arrayOrd[]     = $n[5];
                     if ($k == 0) { // si k= item   es igual a 0 no hay item, por consiguente es improductivo
                        $n[7] = '**'; //envío notificacion de que es un tiempo improductivo
                        $nuevobloq[$n[2]]    = array($n, $b[$i + 1]);
                     } else {
                        $nuevobloq[$n[2]]    = array($n, $b[$i + 1]);
                     }
                  }
               }
            }
            ksort($nuevobloq);
            unset($bloques);
            foreach ($nuevobloq as $k) {
               $tmp[] = $k[0];
               $tmp[] = $k[1];
            }
            $nuevobloq = $tmp;
            unset($tmp);
            foreach ($nuevobloq as $i => $r) {
               $op             = $r[0];
               // $linea          = $r[4] . ' (' . $r[1] . ')'; 
               $linea          = $r[4];
               $item          =  $r[5];
               if ($r[3] == 'I')  $ini = $r[2];
               if ($r[3] == 'F')  $fin = $r[2];
               if (isset($ini) && isset($fin)) {
                  if (isset($improd)) {
                     $fin    = $nuevobloq[$i + 1][2]; 
                     if(!isset($fin)){
                        $fin = $this->capturaFin( $r[20], $ini );
                     }
                     
                     $linea  = '<b>Tiempo Improd.</b> ';
                     $tmp    = array($ini, $fin, $linea, $op, 20 => $r[20], 23 => $r[23], 25 => $item, 26 => '(' . $r[4] . ')', 30 => 'i'); // si 30 en p es productivo, si 30 es i es imprdcutivo
                     unset($improd);
                  } else {
                    
                     $tmp    = array($ini, $fin, $linea, $op, 20 => $r[20], 23 => $r[23], 26 => '(' . $r[1] . ')', 27=>$r[27], 25 => $item, 28=>$r[28],  30 => 'p'); // se inserta cCopias 27 - referencia interna 28 
                     if (isset($r[6])) $tmp[4] = 'NoFin'; //Envio notificación de un item iniciado pero no terminado
                  }
                  unset($ini, $fin);
                  $intervalos[] = $tmp;
               }
               if (isset($r[7])) $improd = true;
            }
            $r = [];
            
           
            if (count($intervalos) != 0) {
               $tmp            = $this->unificaItems($intervalos);
               $tmp            = $this->restaTiempos($tmp);
            }
         }else{
            $tmp =['response_status'=>'error','response_msg'=>'No hay registros de usuario en el perido solicitado'];
         }
      }
      $tmp['users']       = $this->odbc->traeEmpleado();
      return $tmp;
   }

   public function restaTiempos(array $a) {     
      $css=[
         'p'=>'#3ab342',
         'i'=>'#ee1717',
         'j'=>'#174eee'
      ];
      if($this->jornada){
         foreach ($this->jornada as $d) { // inserta jornada en el array en primera posicion
            array_unshift($a, $d);
         }
      }

      foreach ($a as $i => $d) {
         $r[]       = $d;
         $r[$i][21] = (strtotime($d[1] ?? 0)  - strtotime($d[0] ?? 0));                      // segundos           
         $r[$i][22] = human_time(strtotime($d[1] ?? 0) - strtotime($d[0] ?? 0), 1, 1, 1);   // humain time 
         $r[$i][30] = $d[30]; // grupo

      }
      foreach ($r as $d) $g[$d[20]][$d[30]][] = $d; // agrupa por tipo de tiempo productivo e improductivo

      foreach ($g as $user=> $d) {
         foreach ($d as $grupo=> $e) {
            foreach ($e as   $i => $f) {
               $total[$user][$grupo] = (human_time(array_sum(array_column($e, 21)), 1, 1, 1)); // suma tiempo productivo e improductivo
               // crea array de css
               if($e[$i + 1][30] !=$f[30]){
                  $cs[$f[20]][] = $css[$f[30]];
               }
            }
         }

         foreach( $cs as  $user=>$d ){
            $tmp[$user] = '["'. implode('","',$d).'"]' ; // css
         }

      }
      $a = [];
      foreach ($r as $d) $a[$d[20]][] = [$d[0], $d[1], $d[2], $d[3], $d[4],  $d[22], $d[26], $d[30], ($d[27]??''), ($d[28]??'' )  ]; // 22 = human_time de diff no cambiar orden
      $r = ['total' =>  $total, 'tabla' => $a, 'css'=>$tmp];
      $r = $this->verifResut($r);
      return $r;
   }

   public function unificaItems(array $a){
      foreach ($a as $i => $d) {
         if ($d[1] == $a[$i + 1][0] && $d[25] == $a[$i + 1][25]) { // si la hora final es igual a la hora inicial del siguente  y el item es el mismo
            $aF[] = [$d[0], $a[$i + 1][1], $d[2], $d[3], $d[23], 20=>$d[20], 25=>$d[25], 26=>$d[26] ,27=>$d[27], 28=>$d[28], 30=>$d[30] ]; // toma la inicial y la final de siguiente
         } else {
            $aF[] = [$d[0], $d[1], $d[2], $d[3], $d[23], 20=>$d[20], 25=>$d[25], 26=>$d[26], 27=>$d[27], 28=>$d[28], 30=>$d[30]];
         }
      }
      return $aF;
   }


   public function capturaFin($id_us, $ini){
      // Valida todos los posibles cierres y retorna la fecha de cierre
      $dato[0] =  'LEFT(fecha ,16) as fecha';
      $dato[1] =   $id_us; 
      $dato[2] = ' AND fecha BETWEEN "' . date('Y-m-d', strtotime($ini)) . '" AND "' . date('Y-m-d', strtotime($ini . "+1 day")) . '"';
      $dato[3] = ' AND tipo= "s"';
      $dato[4] = ' LIMIT 1 ';
      $oM = $this->odbc->consMarcacionEmp($dato);
      if ($oM->num_rows != 0) {
         $f = ($oM->row[0]);
      } else {
         //No encuentra marcacion, busca el turno de ese dia en salida
         $oM = $this->odbc->consTurnoEmp([$id_us, date('Y-m-d', strtotime($ini))]);
         if ($oM->num_rows != 0) {
            $f = ($oM->row[0]);
         } else {
            // No encuentra turno ni marcacion, cierra a las 6 pm
            $f = (date('Y-m-d', strtotime($ini)) . ' 18:00');
         }
      }
      return  $f;
   }


   public function verifResut(array $ar){
      if (isset($ar) &&  !empty($ar)) {
         return ['response_status' => 'ok', 'response_msg' => $ar];
      } else {
         return ['response_status' => 'error', 'response_msg' => 'No hay datos'];
      }
   }
}
