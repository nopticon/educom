<?php

require_once('../conexion.php');

$textfield = $_REQUEST['textfield'];


foreach ($textfield as $alumno => $codigo) {
	if (!$codigo) continue;

	$sql_update = array(
		'codigo_alumno' => $codigo
	);

	$sql = 'UPDATE alumno SET' . $db->sql_build('UPDATE', $sql_update) . sql_filter('
		WHERE id_alumno = ?', $alumno);
	$db->sql_query($sql);
}

redirect('/adm/codigo_alumno/');