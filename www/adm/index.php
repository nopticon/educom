<?php

require_once('./conexion.php');

if (!isset($_SESSION['userlog'])) {
	header('Location: login.php');
	exit;
}

encabezado();

?>

<div id="logo"></div>

<?php pie(); ?>