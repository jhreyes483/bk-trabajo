<?

class c_reportes {
    public $db;
    function __construct(){
       $_SESSION['s_sysIneditto'] = $_SESSION['s_vEmp'] = 'javier';
       require_once('incl/mySQLi.class.php');
       //$this->db = new c_MySQLi($_SERVER['DOCUMENT_ROOT'] . '/incl/datos_conex5.php');
       $this->db = new c_MySQLi();
    }


    public function datos(){
        $sql ='SELECT IFNULL(I.id_item,0), O.nOrden,
LEFT(I.referencia,40), 
AV.hora,
if(AV.periodo="R","I", IF(AV.periodo="P","F",AV.periodo)) AS periodo,
IFNULL(A.nombre,P.pedido),
CONCAT(E.emp_nombre, " ", E.emp_apellidos),
AV.id
FROM dt_prod_avances AV
LEFT JOIN dt_ordenes_items I ON I.id_item = AV.id_item
LEFT JOIN dt_ordenes O ON I.nOrden = O.nOrden
LEFT JOIN dt_tipospedido P ON P.cod_pedido = AV.seccion
LEFT JOIN dt_sys_ajustes A ON A.valor2 = AV.seccion AND A.tipo="MINUT"
LEFT JOIN dt_empleado E ON E.id_empleado = AV.id_empleado
WHERE AV.periodo != "D" AND AV.id_item IN (648675,648686,648689,651948,0,651944,651943) AND AV.id_empleado =577 AND AV.hora BETWEEN "2021-03-02" AND "2021-03-02 23:59:59"
ORDER BY AV.hora, FIELD (AV.periodo,"I","R","P","F")';

      $dato[2]	= ' AND AV.hora BETWEEN "'.$fechaI.'" AND "'.$fechaF .' 23:59:59"';

      

    }
}


$obj = new c_reportes; 

        $dato[1]    = ' AND AV.id_empleado =' . $id_e ;
        $sql		= $qSQL->m_consulta(117, $dato);
        $resul		= $db->m_trae_array($sql);
       
        if($resul->num_rows>0){
            $dato[3]    = $resul->row[0];
            $sql		= $qSQL->m_consulta(118, $dato);
            $resul      = $db->m_trae_array($sql);
            foreach($resul->rows as $r){
                $bloques[$r[0]][] = array($r[1],$r[2],$r[3],$r[4],$r[5],$r[7]);
            }
        }
        c_mysqli::ver($sql);
        c_mysqli::ver($bloques);
        $nombreEmpl = $resul->row[6];
        $ultHora    = $fechaF .' 23:59:59';
        //Si hay un grupo impar, agrego otro elemento para finalizar lo iniciado
        foreach($bloques as $k=>$b){
            $totItems = count($b);
            $impar = ($totItems%2 ==1? true:false);
            if($impar && $k>0){
                $bloques[$k][] = array($b[0][0], $b[0][1], $ultHora, 'F', $b[0][4], $b[0][5], '*INSERTADO');
            }else if($k==0){ //Verifico las marcaciones de tiempo improductivo, pueden ser pares
                $tot = count($b);
                if($impar) $tot++;
                for($i=0; $i<=$tot; $i++)   { //Agrego Horas de finalización
                    $tmp = array(array($bloques[$k][$i][0], $bloques[$k][$i][1], $bloques[$k][$i][2], 'F', $bloques[$k][$i][4], $bloques[$k][$i][5], '*INSERTADO IMP*')); 
                    array_splice($bloques[$k], ($i+1), 0, $tmp);
                    $i++;
                }
                unset($tmp);
            }
        }
       //muestra($bloques);
        foreach($bloques as $k=>$b){
            foreach($b as $i=>$n){
                if($n[3]=='I') {
                    $arrayOrd[]     = $n[5]; 
                    if($k==0) {
                        $n[7] = '**'; //envío notificacion de que es un tiempo improductivo
                        $nuevobloq[strtotime($n[2])]    = array($n,$b[$i+1]);
                    }else{
                        $nuevobloq[strtotime($n[2])]    = array($n,$b[$i+1]);
                    }
                }
            }
        }
        
        ksort($nuevobloq);
        //muestra($nuevobloq);
        unset($bloques);
       
        foreach($nuevobloq as $k){
            $tmp[] = $k[0];
            $tmp[] = $k[1];
        }
        //muestra($tmp);
        $nuevobloq = $tmp;
        unset($tmp);

        //muestra($nuevobloq);
        foreach($nuevobloq as $i=>$r){
            $op             = $r[0];
            $linea          = $r[4] .' ('.$r[1].')';
            if($r[3]=='I')  $ini = $r[2];  
            if($r[3]=='F')  $fin = $r[2]; 

            if(isset($ini) && isset($fin)){
                if(isset($improd)){
                    $fin    = $nuevobloq[$i+1][2];
                    $linea  = '<b>Tiempo Improd.</b> ('.$r[4].')';
                    $tmp    = array($ini, $fin, $linea, $op);
                    unset ($improd);    
                }else{
                    $tmp    = array($ini, $fin, $linea, $op);
                    if(isset($r[6])) $tmp[4] = 'NoFin'; //Envio notificación de un item iniciado pero no terminado
                }
                 unset ($ini, $fin);
                $intervalos[] = $tmp;
            }

            if(isset($r[7])) $improd = true;
        }
        
        //muestra($intervalos[0][0] . ' / '. $ultHora);
        $tiempo	    = restaTiempos($intervalos[0][0], $ultHora);
        $inicia     = date('G', strtotime($intervalos[0][0]));
        $totalReg 	= $resul->num_rows;
        unset($resul);