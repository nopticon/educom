<?php

require_once('../conexion.php');

$area = $_REQUEST['area'];
$observacion = $_REQUEST['observacion'];

$sql_insert = array(
	'nombre_area' => $area,
	'observacion_area' => $observacion
);
$sql = 'INSERT INTO areas_cursos' . $db->sql_build('INSERT', $sql_insert);
$db->sql_query($sql);

redirect('index.php');