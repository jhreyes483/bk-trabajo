<?php
/*****************************
 *   modificacion: JHR     Fecha: 2021-03-02 
 *   Descripcion:  Desarrollo de cuentas por pagar CCXCP 
 *******************************/
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL & ~E_NOTICE);
require_once($_SERVER['DOCUMENT_ROOT'].'/_modeloClass/contabilidadModelo.class.php' );
// echo 'controller'; die();
  class  c_Contabilidad extends c_ConsultasDB{
   private $odbc; 
   public function __construct()
   {
      $this->odbc = new c_ConsultasDB; 
   }
   // public function consClientes(){
   //    return $this->odbc->consClientes();
   // }

   // public function consFacturaCompra(){
   //    return $this->odbc->consFacturaConpra($_POST['users']);
   // }


   public function traeDatos(){
      $r=[];
      $tmp = $this->odbc->consClientes(); // select de proveedores
      $r['proveedores'] = $tmp;
      if($_POST['users']){
         $tmp = $this->odbc->consFacturaConpra($_POST['users']); // consulta factura
         $tmp = $this->verifResut($tmp);
         $r['FC'] = $tmp ;
      }
      return $r;
   }

   public function verifResut(array $ar){
      if(isset($ar) &&  !empty($ar)){
         return ['response_status' => 'ok', 'response_msg'=>$ar];
      }else{
         return['response_status' => 'error', 'response_msg'=>'No hay datos'];
      }
   }



 
 
 
 
 }



?>