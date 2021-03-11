<?
die('entro a funciones');

function acortaTexto($text, $limite = 20) {  
	if(strlen($text)<=$limite) return $text;
	$expresionregular = "/(^.{0,".$limite."})(\W+.*$)/";
	$reemplazo = "\${1}";
	$texto = preg_replace($expresionregular, $reemplazo, $text);
	$texto = $texto.'...';  
	return $texto; 
}  

//Retorna la diferencia de dos tiempos (en segundos)
function restaTiempos($tIni, $tFin) { //deben llegar en formato string

	if($tIni=='' || $tFin=='') return 0;
	if($tIni==$tFin) return 0;
	$tIni = strtotime($tIni);
	$tFin = strtotime($tFin);
	$dif = $tFin-$tIni;
	return $dif;
}

function Horas2Decimal($tmp, $dec, $sd='.', $sm=','){
	$array	= explode(':', $tmp);
	$min	= (abs($array[0])*60) + ($array[1]??0);
	$hora	= number_format(($min/60),$dec,$sd,$sm);
	return  $hora;
}


function human_time($input_seconds, $rs=1, $normal=0, $mostrarD=0) { //$rs = muestra segundos // normal=1 deja h m s  como abreviaturas // mostrarD=1 muestra días
	$days		   = floor($input_seconds / 86400);
	$remainder	= floor($input_seconds % 86400);
	$hours		= floor($remainder / 3600);
	$remainder	= floor($remainder % 3600);
	$minutes	   = floor($remainder / 60);
	$seconds	   = floor($remainder % 60);

	if($mostrarD>0){
		$hours	+= $days*24; 
		$days	= '';
	}else{
		$days	= ($days > 0)? 			$days .($normal==0?' días':'d') :'';
	}
	$hours	= ($hours > 0)? 			$hours.($normal==0?' horas':'h'):'';
	$minutes	= ($minutes > 0)?			$minutes.($normal==0?' min':'m'):'';
	$seconds	= ($seconds > 0 && $rs==1)? $seconds.($normal==0?' seg':'s'):'';
	return "$days $hours $minutes $seconds";
}

function humanTimeExcel($input_seconds, $var = 0,   $normal = 0 )
{ 
   //$rs = muestra segundos 
   // normal=1 deja h m s  como abreviaturas 
   // mostrarD=1 muestra d?as
//  $hours = floor($input_seconds / 3600);

   switch ($var) {
      case 0:
         // Retorna horas con decimales
         $hours = $input_seconds / 3600;
         $hours = number_format($hours, 2, '.', '');
         return $hours.'  '; 
      break;
      case 1:
         $remainder = floor($input_seconds  % 3600);
         $minutes   = floor($remainder / 60);
         $hours     = floor($input_seconds / 3600);
         $hours     = ($hours > 0) ? $hours . ($normal == 0 ? '.' : 'h') : '';
         $minutes   = ($minutes > 0) ? $minutes . ($normal == 0 ? '.' : 'm') : '';
         $hours??0;
         $minutes??0;
     return "$hours h $minutes m";
      // return "$hours h $minutes m";
      break;
   }

}


function f_s2h($seg, $retS=0){ //recibe segundos, retorna tiempo formateado
	$h		   = floor($seg/3600);
	$m		   = (($seg/60) % 60);
	$s		   = ($seg%60);
	$tiempo  = ($retS>0? sprintf('%02d:%02d:$02d', $h, $m,$s): sprintf('%02d:%02d', $h, $m));
	return $tiempo;
}

function f_edad($nacimiento){
	if(!is_date($nacimiento) || $nacimiento=='0000-00-00') return '';
	list($ano,$mes,$dia) = explode("-",$nacimiento);
	$ano_now = date("Y") - $ano;
	$mes_now = date("m"); 
	$dia_now = date("d"); 
	
	if($mes_now < $mes) $ano_now--;
	if(($mes == $mes_now) && ($dia_now<$dia)) $ano_now--;
	
	return $ano_now;
}

if(!function_exists('is_date')){
	function is_date($in) {
		return (boolean)strtotime($in);
	}
}


function f_es_email($em){
	return preg_match('/^[^@]+@[a-zA-Z0-9._-]+.[a-zA-Z]+$/', $em);	
}


function f_discriminaHoras($hora1, $hora2){
	include_once($_SERVER['DOCUMENT_ROOT'].'/_classes/festivos.class.php');
   $fest = new c_Festivos();
   include_once($_SERVER['DOCUMENT_ROOT'].'/soloITT.php');
	$horaI 	= strtotime($hora1);
	$horaF 	= strtotime($hora2);
	$horasD	= $horasN = $horasFD = $horasFN  = 0;
   $terNoct  = 6;  // Hora en que terminana las nocturnas
   $iniNoct  = 21; // Hora en que inician las nocturnas
   
	while(true){
      $fecha   = date('Y-m-d', $horaI);
      
      $festivo    = $fest->f_esFestivo($fecha,1);
      $festivo    = ($festivo[0] ==1)? true:false;
      
      $intHora = date('G', $horaI);
      $intMin  = 60-(abs(date('i', $horaI)));
      
      if($intMin==0) $intMin = 60; // Verificar por que esto se vuelve 60
		if($festivo == false){
			if($intHora>=$terNoct && $intHora<$iniNoct){
            $horasD += $intMin; 
            $ultimo  = 'D';
			}
         if($intHora>=$iniNoct || $intHora<$terNoct) {
            $horasN += $intMin;  
            $ultimo  = 'N';
         }
		}else{
			if($intHora>=$terNoct && $intHora<$iniNoct){
				 $horasFD+= $intMin; 
				 $ultimo = 'FD';
			}
			if($intHora>=$iniNoct || $intHora<$terNoct) {
				$horasFN+= $intMin;  
				$ultimo = 'FN';
			}
		}
		$horaI	= strtotime("+$intMin minutes", $horaI);
         
      if($horaI>=$horaF) break;
	}
   
	$intMinU		= (abs(date('i', $horaF))); //Verifico la ultima hora, por si tiene minutos
	if($intMinU>0) {
		switch ($ultimo){
			case 'D':	$horasD  = $horasD-$intMin+$intMinU;	break;
			case 'N':	$horasN  = $horasN-$intMin+$intMinU;	break;
			case 'FD':	$horasFD = $horasFD-$intMin+$intMinU;	break;
			case 'FN':	$horasFN = $horasFN-$intMin+$intMinU;	break;
		}
	}
	return array($horasD, $horasN, $horasFD, $horasFN);
}


//Permite calcular las lineas que debe tener una area de texto segun su contenido
function f_linTextArea($text, $ancho){
	if (strtoupper(substr(PHP_OS,0,3)=='WIN')) { 
		$eol="\r\n"; 
	} elseif (strtoupper(substr(PHP_OS,0,3)=='MAC')) { 
		$eol="\r"; 
	} else { 
		$eol="\n"; 
	} 
	$cad	= wordwrap($text, $ancho, $eol, 1); 
	$lineas	= max(1,substr_count($cad,$eol)); 
	return $lineas;	
}
?>