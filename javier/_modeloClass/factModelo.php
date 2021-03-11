<?php

class c_reportes {
   public $db;
   function __construct(){
      $_SESSION['s_sysIneditto'] = $_SESSION['s_vEmp'] = 'javier';
      require_once('incl/mySQLi.class.php');
      $this->db = new c_MySQLi($_SERVER['DOCUMENT_ROOT'] . '/incl/datos_conex5.php');
   }
   
   public function consultaJornada($id, $fIni, $fFin){
      $sql = 'SELECT
         LEFT(CONCAT(E.emp_nombre, " ", E.emp_apellidos), 15), 
         I.fecha, 
         I.tipo,  I.motivo, 
         CASE motivo 
            WHEN 1 THEN "JORNADA" 
            WHEN 2 THEN "ALMUERZO" 
            WHEN 3 THEN "DESCANSO" 
            WHEN 4 THEN "PERMISO" 
            WHEN 5 THEN "CITA MEDICA" 
            WHEN 6 THEN "LABOR EXTERNA" 
            WHEN 7 THEN "HORAS EXTRAS" 
            END,
         "" , "" , "", "", ""
         FROM dt_sys_ingresoLog  I 
         LEFT JOIN dt_empleado E USING(id_empleado) 
         WHERE I.fecha BETWEEN "'. $fIni.'" AND "'.$fFin.'" 
         AND id_empleado = '.$id.' AND I.motivo >0
         ORDER BY fecha , motivo';      
      return $this->db->m_trae_array($sql)->rows;   
   }
   
   public function consTiempos($id, $fIni, $fFin){
      $sql = 'SELECT  LEFT(CONCAT(E.emp_nombre, "" , E.emp_apellidos), 15), 
         DA.hora,  DA.periodo , "" , OI.referencia, DA.id_item, DA.seccion , CONCAT("TI / ", DSA.nombre), OI.nOrden,
         DP.pedido
         FROM dt_empleado E
         LEFT JOIN dt_prod_avances DA USING(id_empleado)
         LEFT JOIN dt_ordenes_items OI USING(id_item) 
         LEFT JOIN dt_tipospedido DP USING(cod_pedido)
         LEFT JOIN dt_sys_ajustes DSA ON DSA.valor2 =  DA.seccion
         WHERE DA.hora BETWEEN "'.$fIni.'" AND "'.$fFin.'" 
         AND E.id_empleado = ' .$id.'
         ORDER BY motivo, hora';
      return $this->db->m_trae_array($sql)->rows;
   }


// dashboarcd-por-maquina
// captura fecha de maqunas tiempo disponible
public function consFechMaquiDispDB($id_maq, $fIni, $fFin){
   $sql = 'SELECT id_maquina, fecha, desde, hasta
      FROM dt_prodDispoMaqui
      WHERE id_maquina IN( '.$id_maq.' )
      AND fecha
      BETWEEN "'.$fIni.'" AND "'.$fFin.'"
      ';
   return $this->db->m_trae_array($sql)->rows;
}


   public function traeEmpleado(){
      $sql = "SELECT  
         id_empleado,   CONCAT( emp_nombre, ' ', emp_apellidos   )
         FROM dt_empleado E
         WHERE cargo_id IN ( 3, 5, 25, 24, 27, 43, 67, 76, 
                           80, 85, 88, 89, 90, 94, 96, 97)
         ORDER BY emp_nombre";
      return $this->db->m_trae_array($sql)->rows;
   }
   public function TraeTodosLosDatos($id, $fIni, $fFin){
      $sql = 'SELECT DISTINCT
         LEFT(CONCAT(E.emp_nombre, " ", E.emp_apellidos), 15), 
         I.fecha, 
         I.tipo,  I.motivo, 
         CASE motivo 
            WHEN 1 THEN "JORNADA" 
            WHEN 2 THEN "ALMUERZO" 
            WHEN 3 THEN "DESCANSO" 
            WHEN 4 THEN "PERMISO" 
            WHEN 5 THEN "CITA MEDICA" 
            WHEN 6 THEN "LABOR EXTERNA" 
            WHEN 7 THEN "HORAS EXTRAS" 
            END,
         "" , "" , "", "", ""
         FROM dt_sys_ingresoLog  I 
         LEFT JOIN dt_empleado E USING(id_empleado) 
         WHERE I.fecha BETWEEN "'. $fIni.' 00:00:00" AND "'.$fFin.' 23:59:59" 
         AND id_empleado IN ('.$id.') AND I.motivo >0
         UNION
         SELECT DISTINCT  LEFT(CONCAT(E.emp_nombre, "" , E.emp_apellidos), 15), 
         DA.hora,  DA.periodo, "", OI.referencia, DA.id_item, DA.seccion , CONCAT("TI / ", DSA.nombre), OI.nOrden,
         DP.pedido
         FROM dt_empleado E
         LEFT JOIN dt_prod_avances DA USING(id_empleado)
         LEFT JOIN dt_ordenes_items OI ON OI.id_item = DA.id_item 
         LEFT JOIN dt_tipospedido DP   ON DP.cod_pedido = OI.cod_pedido
         LEFT JOIN dt_sys_ajustes DSA ON DSA.valor2 =  DA.seccion AND seccion > 1000
         WHERE DA.hora BETWEEN "'.$fIni.'  00:00:00" AND "'.$fFin.' 23:59:59" 
         AND E.id_empleado IN ('.$id.')
         ORDER BY fecha, motivo';
      return $this->db->m_trae_array($sql)->rows;
   
   }
   public function consMaquinasT(){
      $sql = 'SELECT M.id_maquina,  
         CONCAT(T.id_tipoMaquina, " / ", T.nom_tipoMaquina, " - " , M.nombre_maquina),
         T.id_tipoMaquina
         FROM dt_cot_maquinas M 
         INNER JOIN dt_cot_tipoMaquina T USING(id_tipoMaquina)
         ORDER BY T.nom_tipoMaquina, nombre_maquina';
      return $this->db->m_trae_array($sql)->rows;
   }


// Primera consulta
   public function consFechaMaquina($id, $fIni, $fFin, $campo){
      $sql = 'SELECT DISTINCT A.id_item, A.hora, A.periodo, "", 
         C.maquina , M.nombre_maquina, A.f'.$campo.', 
         T.id_tipoMaquina,  T.nom_tipoMaquina, 
         A.id_empleado

            FROM dt_ordenes_itemsC C
            INNER JOIN dt_prod_avances A USING(id_item)
            INNER JOIN dt_cot_maquinas M ON M.id_maquina = C.maquina
            INNER JOIN dt_cot_tipoMaquina  T ON T.id_tipoMaquina  = M.id_tipoMaquina 
            WHERE C.maquina IN ('.$id.') 
            AND A.hora BETWEEN "'.$fIni.' 00:00:00" AND "'.$fFin.' 23:59:59" 
            UNION
            SELECT DISTINCT A.id_item, A.hora, A.periodo, 
            "", MS.id_maquina , M.nombre_maquina, A.f'.$campo.', 
            T.id_tipoMaquina, T.nom_tipoMaquina,
            A.id_empleado 

            FROM dt_cot_maquinasSustratos MS
            INNER JOIN dt_ordenes_items OI ON MS.id_producto = OI.id_producto
            INNER JOIN dt_prod_avances A ON OI.id_item = A.id_item
            INNER JOIN dt_cot_maquinas M ON MS.id_maquina = M.id_maquina 
            INNER JOIN dt_cot_tipoMaquina T ON M.id_tipoMaquina = T.id_tipoMaquina
            WHERE MS.id_maquina IN ('.$id.') 
            AND T.id_tipoMaquina = 10
            AND A.hora BETWEEN "'.$fIni.' 00:00:00" AND "'.$fFin.' 23:59:59" 
            ORDER BY id_tipoMaquina, maquina  ,hora
         ';        
      return $this->db->m_trae_array($sql)->rows;
   }




//-----------------------------------------------------------------------



   public function consFases($cod_pedido){
      $sql = 'SELECT cod_pedido, fase1, fase2, fase3, fase4, fase5, fase6, fase7, fase8  
         FROM dt_tipospedido
         WHERE cod_pedido = "'.$cod_pedido.'"
         LIMIT 1';
      return $this->db->m_trae_array($sql)->row;
   }


/* comentado 01 oct 2020  */
   public function consTipoMaq(){
      $sql = 'SELECT id_tipoMaquina, nom_tipoMaquina
         FROM  dt_cot_tipoMaquina
         ';
      return $this->db->m_trae_array($sql)->rows;
   }
   

   // por id trae las maqunas
   public function consPorTipo($id){
      $sql = 'SELECT  T.nom_tipoMaquina, M.nombre_maquina
         FROM dt_cot_maquinas M 
         INNER JOIN dt_cot_tipoMaquina T USING(id_tipoMaquina)
         WHERE id_tipoMaquina IN ('.$id.')';
      return $this->db->m_trae_array($sql)->rows;
   }




   // 01 OCT FINAL VERIFICADA
   public function consFechaMaquinaTipo($id, $fIni, $fFin, $campo){
     // echo '<h1>consulta consFechaMaquinaTipo </h1>';
      $sql = 
      'SELECT DISTINCT A.id_item, A.hora, A.periodo,
      "", C.maquina , M.nombre_maquina,
         A.f'.$campo.', T.id_tipoMaquina, T.nom_tipoMaquina, 
         A.id_empleado , rendimProm  
         FROM dt_ordenes_itemsC C
         INNER JOIN dt_prod_avances A USING(id_item)
         INNER JOIN dt_cot_maquinas M ON M.id_maquina = C.maquina
         INNER JOIN dt_cot_tipoMaquina  T ON T.id_tipoMaquina  = M.id_tipoMaquina 
         AND T.id_tipoMaquina  = '.$id.' 
         AND 
         A.hora 
         BETWEEN "'.$fIni.' 00:00:00" AND "'.$fFin.' 23:59:59" 
         UNION
      SELECT DISTINCT A.id_item, A.hora, A.periodo, 
      "", MS.id_maquina , M.nombre_maquina, 
      A.f'.$campo.',  T.id_tipoMaquina, T.nom_tipoMaquina, 
      A.id_empleado , rendimProm 
         FROM dt_cot_maquinasSustratos MS
         INNER JOIN dt_ordenes_items OI ON MS.id_producto = OI.id_producto
         INNER JOIN dt_prod_avances A ON OI.id_item       = A.id_item
         INNER JOIN dt_cot_maquinas M ON MS.id_maquina    = M.id_maquina 
         INNER JOIN dt_cot_tipoMaquina T ON M.id_tipoMaquina = T.id_tipoMaquina
         AND T.id_tipoMaquina  = '.$id.' 
         AND T.id_tipoMaquina = 10
         AND hora
         BETWEEN "'.$fIni.' 00:00:00" AND "'.$fFin.' 23:59:59" 
         ORDER BY id_tipoMaquina, maquina  ,hora
         ';        
      return $this->db->m_trae_array($sql)->rows;
   }
   

   public function consFechaMaquinaTipo2($fIni, $fFin, $campo){
      //echo '<h1>Consulta consFechaMaquinaTipo2</h1>';
      $sql = 
      'SELECT DISTINCT A.id_item, A.hora, A.periodo, 
      "", C.maquina , M.nombre_maquina, 
      A.f'.$campo.', T.id_tipoMaquina, T.nom_tipoMaquina, 
      A.id_empleado, rendimProm  
         FROM dt_ordenes_itemsC C
         INNER JOIN dt_prod_avances A USING(id_item)
         INNER JOIN dt_cot_maquinas M ON M.id_maquina = C.maquina
         INNER JOIN dt_cot_tipoMaquina  T ON T.id_tipoMaquina  = M.id_tipoMaquina 
         AND
         A.hora 
         BETWEEN "'.$fIni.' 00:00:00" AND "'.$fFin.' 23:59:59" 
         UNION
      SELECT DISTINCT A.id_item, A.hora, A.periodo, 
      "", MS.id_maquina , M.nombre_maquina, 
      A.f'.$campo.', T.id_tipoMaquina, T.nom_tipoMaquina,  
      A.id_empleado, rendimProm  
         FROM dt_cot_maquinasSustratos MS
         INNER JOIN dt_ordenes_items OI ON MS.id_producto = OI.id_producto
         INNER JOIN dt_prod_avances A ON OI.id_item = A.id_item
         INNER JOIN dt_cot_maquinas M ON MS.id_maquina = M.id_maquina 
         INNER JOIN dt_cot_tipoMaquina T ON M.id_tipoMaquina = T.id_tipoMaquina
         AND T.id_tipoMaquina = 10
         AND hora
         BETWEEN "'.$fIni.' 00:00:00" AND "'.$fFin.' 23:59:59" 
         ORDER BY id_tipoMaquina, maquina  ,hora
         ';        
      return $this->db->m_trae_array($sql)->rows;
   }
   

      // Optiene el tiempo periodo de la operacion apartir de fecha y id usuario
      public function tiemTotPorIdEmp($sIdEmp, $fIni, $fFin  ){
       //  echo '<h1>tiemTotPorIdEmp</h1>';
         $sql = 
         'SELECT OC.maquina, DA.hora, DA.periodo, 
         OI.referencia, DA.id_item, DA.seccion , 
         CONCAT("TI / ", DSA.nombre), OI.nOrden, 
         DP.pedido, E.id_empleado
               FROM dt_empleado E 
               LEFT JOIN dt_prod_avances DA 
               USING(id_empleado) 
               LEFT JOIN dt_ordenes_items OI ON OI.id_item  = DA.id_item 
               LEFT JOIN dt_tipospedido DP ON DP.cod_pedido = OI.cod_pedido 
               LEFT JOIN dt_ordenes_itemsC OC ON OI.id_item = OC.id_item 
               LEFT JOIN dt_sys_ajustes DSA ON DSA.valor2   = DA.seccion 
               AND DA.seccion > 1000 
               WHERE DA.hora 
               BETWEEN "'.$fIni.' 00:00:00" AND "'.$fFin.' 23:59:59" 
               AND E.id_empleado IN ('.$sIdEmp.') 
               UNION
            SELECT DISTINCT MS.id_maquina, A.hora, A.periodo,
            OI.referencia, A.id_item, A.seccion,
            CONCAT("TI / ", DSA.nombre), OI.nOrden,
            "", A.id_empleado
               FROM dt_cot_maquinasSustratos MS 
               LEFT JOIN dt_ordenes_items OI ON MS.id_producto = OI.id_producto 
               LEFT JOIN dt_prod_avances A ON OI.id_item = A.id_item 
               LEFT JOIN  dt_sys_ajustes DSA ON DSA.valor2  = A.seccion
               LEFT JOIN dt_cot_maquinas M ON MS.id_maquina = M.id_maquina 
               LEFT JOIN dt_cot_tipoMaquina T ON M.id_tipoMaquina = T.id_tipoMaquina 
               WHERE A.id_empleado IN ('.$sIdEmp.') 
               AND T.id_tipoMaquina = 10 AND A.hora 
               BETWEEN "'.$fIni.' 00:00:00" AND "'.$fFin.' 23:59:59" 
               ORDER BY hora, maquina
               ';        
         return $this->db->m_trae_array($sql)->rows;
      }


      // consulta de tiempo total maquinas 3 - nov
      public function jornTotMaqDB($sIdEmp, $fIni, $fFin  ){
         //  echo '<h1>tiemTotPorIdEmp</h1>';
         $sql = 
            'SELECT DISTINCT 
            IF( OI.id_item >0 ,OC.maquina , DA.idMaquina ), 
            DA.hora, DA.periodo,  OI.referencia, 
            DA.id_item, DA.seccion ,   CONCAT("TI / ", DSA.nombre), 
            OI.nOrden, DP.pedido,  E.id_empleado, 
            OC.maquina 
               FROM dt_empleado E 
               LEFT JOIN dt_prod_avances DA 
               USING(id_empleado) 
               LEFT JOIN dt_ordenes_items OI ON OI.id_item  = DA.id_item 
               LEFT JOIN dt_tipospedido DP ON DP.cod_pedido = OI.cod_pedido 
               LEFT JOIN dt_ordenes_itemsC OC ON OI.id_item = OC.id_item 
               LEFT JOIN dt_sys_ajustes DSA ON DSA.valor2   = DA.seccion 
               AND DA.seccion > 1000 
               WHERE DA.hora 
               BETWEEN "'.$fIni.' 00:00:00" AND "'.$fFin.' 23:59:59" 
               AND E.id_empleado IN ('.$sIdEmp.') 
               UNION
            SELECT DISTINCT  
            IF(A.id_item > 0, MS.id_maquina , A.idMaquina  )
            , A.hora, A.periodo, OI.referencia, 
            A.id_item, A.seccion, CONCAT("TI / ", DSA.nombre), 
            OI.nOrden, "",  A.id_empleado, MS.id_maquina
               FROM dt_cot_maquinasSustratos MS 
               LEFT JOIN dt_ordenes_items OI ON MS.id_producto = OI.id_producto 
               LEFT JOIN dt_prod_avances A ON OI.id_item = A.id_item 
               LEFT JOIN  dt_sys_ajustes DSA ON DSA.valor2  = A.seccion
               LEFT JOIN dt_cot_maquinas M ON MS.id_maquina = M.id_maquina 
               LEFT JOIN dt_cot_tipoMaquina T ON M.id_tipoMaquina = T.id_tipoMaquina 
               WHERE A.id_empleado IN ('.$sIdEmp.') 
               AND T.id_tipoMaquina = 10 AND A.hora 
               BETWEEN "'.$fIni.' 00:00:00" AND "'.$fFin.' 23:59:59" 
               ORDER BY hora, maquina           
               ';        
         return $this->db->m_trae_array($sql)->rows;
      }

}


