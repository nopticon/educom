<?php

require_once('../conexion.php');

$id_alumno = $_REQUEST['id_alumno'];
$tipo_falta = $_REQUEST['tipo_falta'];
$falta = $_REQUEST['falta'];

$sql_insert = array(
	'id_alumno' => $id_alumno,
	'falta' => $falta,
	'tipo_falta' => $tipo_falta,
	'anio_falta' => date('Y')
);
$sql = 'INSERT INTO faltas' . $db->sql_build('INSERT', $sql_insert);
$db->sql_query($sql);

$_SESSION['guardar'] = 1;
redirect('index.php');