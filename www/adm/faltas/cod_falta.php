<?php

require_once('../conexion.php');

$id_alumno = request_var('id_alumno', 0);
$falta = request_var('falta', '');
$tipo_falta = request_var('tipo_falta', '');

if (empty($falta)) {
	location('.');
}

$sql_insert = array(
	'id_alumno' => $id_alumno,
	'falta' => $falta,
	'tipo_falta' => $tipo_falta,
	'anio_falta' => date('Y')
);
$sql = 'INSERT INTO faltas' . $db->sql_build('INSERT', $sql_insert);
$db->sql_query($sql);

$_SESSION['guardar'] = 1;

location('.');