<?php

require_once('../conexion.php');

$id_alumno = $_REQUEST['id_alumno'];
$carne = $_REQUEST['carnet'];
$id_grado = $_REQUEST['grado'];
$id_seccion = $_REQUEST['seccion'];
$encargado = $_REQUEST['encargado'];
$telefonos = $_REQUEST['telefonos'];
$observaciones = $_REQUEST['observacion'];

$status = 'ReInscrito';
$anio = date('Y');

$sql_insert = array(
	'id_alumno' => $id_alumno,
	'carne' => $carne,
	'id_grado' => $id_grado,
	'id_seccion' => $id_seccion,
	'observaciones' => $observaciones,
	'encargado_reinscripcion' => $encargado,
	'telefonos' => $telefonos,
	'status' => $status,
	'anio' => $anio
);
$sql = 'INSERT INTO reinscripcion' . $db->sql_build('INSERT', $sql_insert);
$db->sql_query($sql);

redirect('index.php');