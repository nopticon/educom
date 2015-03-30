<?php

require_once('../../conexion.php');

$id_curso = request_var('id_curso', 0);
$id_grado = request_var('id_grado', 0);

$curso = request_var('curso', '');
$capacidad = request_var('capacidad', 0);
$status = request_var('status', '');

if (empty($curso)) {
	location('../cursos/cursos.php?grado=' . $id_grado);
}

$sql_update = array(
	'nombre_curso' => $curso,
	'capacidad' => $capacidad,
	'status' => $status
);
$sql = 'UPDATE cursos SET' . $db->sql_build('UPDATE', $sql_update) . sql_filter('
	WHERE id_curso = ?', $id_curso);
$db->sql_query($sql);

location('../cursos/cursos.php?grado=' . $id_grado);