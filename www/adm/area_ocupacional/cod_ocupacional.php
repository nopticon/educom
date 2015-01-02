<?php

require_once('../conexion.php');

$area = $_REQUEST['area'];
$observacion = $_REQUEST['observacion'];

$sql_insert = array(
	'nombre_ocupacion' => $area,
	'observacion' => $observacion
);
$sql = 'INSERT INTO area_ocupacional' . $db->sql_build('INSERT', $sql_insert);
$db->sql_query($sql);

redirect('index.php');