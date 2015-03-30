<?php

require_once('../../conexion.php');

$id_examen = request_var('id_examen', 0);

$examen = request_var('examen', '');
$observacion = request_var('observacion', '');
$status = request_var('status', '');

if (empty($examen)) {
	location('../examen/');
}

$sql_update = array(
	'examen' => $examen,
	'observacion' => $observacion,
	'status' => $status,
);
$sql = 'UPDATE examenes SET' . $db->sql_build('UPDATE', $sql_update) . sql_filter('
	WHERE id_examen = ?', $id_examen);
$db->sql_query($sql);

location('../examen/');