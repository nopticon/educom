<?php

require_once('../conexion.php');

$nombre_ocupacion = $_REQUEST['nombre_ocupacion'];

foreach ($nombre_ocupacion as $alumno => $codigo) {
	if (!$codigo) continue;

	$sql = 'SELECT *
		FROM ocupacion_alumno
		WHERE id_alumno = ?';
	if ($row = $db->sql_fieldrow(sql_filter($sql, $alumno))) {
		$sql = 'UPDATE ocupacion_alumno SET id_ocupacion = ?
			WHERE id_alumno = ?';
		$db->sql_query(sql_filter($sql, $codigo, $alumno));
	} else {
		$sql_insert = array(
			'id_ocupacion' => $codigo,
			'id_alumno' => $alumno
		);
		$sql = 'INSERT INTO ocupacion_alumno' . $db->sql_build('INSERT', $sql_insert);
		$db->sql_query($sql);
	}
}

redirect('index.php');