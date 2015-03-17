<?php

require_once('../../conexion.php');

$id_examen = $_REQUEST['id_examen'];

$examen = $_REQUEST['examen'];
$observacion = $_REQUEST['observacion'];
$status = $_REQUEST['status'];

$sql_update = array(
	'examen' => $examen,
	'observacion' => $observacion,
	'status' => $status,
);
$sql = 'UPDATE examenes SET' . $db->sql_build('UPDATE', $sql_update) . sql_filter('
	WHERE id_examen = ?', $id_examen);
$db->sql_query($sql);

redirect('../examen/index.php');