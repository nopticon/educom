<?php

require_once('../conexion.php');

$grado = $_REQUEST['grado'];
// $seccion = $_REQUEST['seccion'];
$status = $_REQUEST['status'];

$sql_insert = array(
	'nombre' => $grado,
	'status' => $status,
	'seccion' => '',
	'fecha_grado' => ''
);
$sql = 'INSERT INTO grado' . $db->sql_build('INSERT', $sql_insert);
$db->sql_query($sql);

redirect('index.php');