<?
//if($_SERVER['SCRIPT_NAME'] == '/incl/datos_conex.php') die();

if(!isset($_SESSION['s_sysIneditto'])) session_start();

$_SESSION['s_sysIneditto'] = 'javier';
$_SESSION['s_vEmp']='javier';
$hostname_conn 	= '192.168.2.9';
$username_conn 	= 'javier';
$password_conn  = 'javier';
$db_conn		     = 'javier';
//$db_conn		= 'ittpruebas3';
?>
