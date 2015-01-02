<?php

require('../conexion.php');

$examen = $_REQUEST['examen'];
$observacion = $_REQUEST['observacion'];
$status = $_REQUEST['status'];

$sql_insert = array(
	'examen' => $examen,
	'observacion' => $observacion,
	'status' => $status
);
$sql = 'INSERT INTO examenes' . $db->sql_build('INSERT', $sql_insert);
$db->sql_query($sql);

redirect('index.php');