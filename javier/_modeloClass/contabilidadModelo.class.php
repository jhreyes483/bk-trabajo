<?php

/*****************************
 *   modificacion: JHR     Fecha: 2021-03-02 
 *   Descripcion:  Desarrollo de cuentas por pagar CCXCP 
*******************************/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);
// require_once();

class  c_ConsultasDB {
   protected $db;
   function __construct(){
      $_SESSION['s_sysIneditto'] = $_SESSION['s_vEmp'] = 'javier';
      require_once($_SERVER['DOCUMENT_ROOT'].'/incl/mySQLi.class.php');
      $this->db = new c_MySQLi($_SERVER['DOCUMENT_ROOT'] . '/incl/datos_conex5.php');
   }
   

   public function consClientes(){
      $sql = 
      'SELECT id_cliente  as id , 
         REPLACE(cli_nombre, "|", " " )  as nombre
         FROM dt_clientes
      WHERE  esProveedor > 0';
      return $this->db->m_trae_array($sql,2);
   }

   public function consFacturaConpra($id_emp){
      echo $sql = 
      'SELECT * FROM dt_ad_factCompra 
         WHERE saldo > 0 
         AND `id_empleado` = '.$id_emp.'';
      return $this->db->m_trae_array($sql)->rows;
   }

}

?>