<?php

require_once('../conexion.php');

$textfield = $_REQUEST['textfield'];


foreach ($textfield as $alumno => $codigo) {
	if (!$codigo) continue;

	$sql_update = array(
		'codigo_alumno' => $codigo
	);

	$sql = 'UPDATE alumno SET' . $db->sql_build('UPDATE', $sql_update) . $db->__prepare('
		WHERE id_alumno = ?', $alumno);
	$db->sql_query($sql);
}

redirect('index.php');