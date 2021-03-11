<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);
class c_informeModel {
   public $db;
   function __construct(){
      $_SESSION['s_sysIneditto'] = $_SESSION['s_vEmp'] = 'javier';
      require_once($_SERVER['DOCUMENT_ROOT'].'/incl/mySQLi.class.php');
      $this->db = new c_MySQLi($_SERVER['DOCUMENT_ROOT'] . '/incl/datos_conex5.php');
   }
   public function consPeriodo(  array $param = null){
      $param =  implode(' ', $param);
   $sql = 
      'SELECT IFNULL(I.id_item,0), O.nOrden,
         LEFT(I.referencia,40), 
         CONCAT(LEFT(AV.hora,16)),
         if(AV.periodo="R","I", IF(AV.periodo="P","F",AV.periodo)) AS periodo,
         IFNULL(A.nombre,P.pedido), 
         CONCAT(E.emp_nombre, " ", E.emp_apellidos),
         AV.id, E.id_empleado,
         "nombre del acabado", 
         I.cCopias, IP.refInterna
         FROM dt_prod_avances AV
            LEFT JOIN dt_ordenes_items I ON I.id_item = AV.id_item
            LEFT JOIN dt_ordenes O ON I.nOrden = O.nOrden
            LEFT JOIN dt_tipospedido P ON P.cod_pedido = AV.seccion
            LEFT JOIN dt_sys_ajustes A ON A.valor2 = AV.seccion AND A.tipo="MINUT"
            LEFT JOIN dt_empleado E ON E.id_empleado = AV.id_empleado
            LEFT JOIN dt_inv_productos IP ON IP.id_producto = I.id_producto
            WHERE AV.periodo != "D"
            '
      .$param;
      return $this->db->m_trae_array($sql);   
   }

   public function traeEmpleado(){
      $sql = 
      'SELECT  
         id_empleado,   CONCAT( emp_nombre," ", emp_apellidos   )
         FROM dt_empleado E
         ORDER BY emp_nombre';
      return $this->db->m_trae_array($sql,2);
   }

   public function consMarcacionEmp($d){
   $sql = 
      'SELECT '.$d[0].' 
         FROM dt_sys_ingresoLog
            WHERE id_empleado IN ('.$d[1].')
            AND motivo = 1 
            '.$d[2].$d[3].$d[4];
         return $this->db->m_trae_array($sql);
      }


      public function consTurnoEmp($d){
         $sql = 
         'SELECT CONCAT( DE.fecha," ", DT.hasta) as fecha
            FROM  dt_rh_turnos DT 
            JOIN dt_empleado_turnos DE USING (id_turno)
            WHERE id_empleado ='.$d[0].'
            AND fecha LIKE "'.$d[1].'%"
         ORDER BY fecha DESC
         LIMIT 1';
      return $this->db->m_trae_array($sql);
      }
   }



// $fechaI = '2021-03-02';
// $fechaF = '2021-03-02 23:59:59';
// $id_em  = 159;
// 
// $obj = new c_informeModel; 
// $r = $obj->consPeriodo($fechaI, $fechaF , $id_em );
