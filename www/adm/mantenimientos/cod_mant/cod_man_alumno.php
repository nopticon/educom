<?php

require_once('../../conexion.php');

$id_alumno 			= request_var('id_alumno', 0);
$carne 				= request_var('carne', '');
$codigo_alumno 		= request_var('codigo_alumno', '');
$nombre 			= request_var('nombre', '');
$apellido 			= request_var('apellido', '');
$direccion 			= request_var('direccion', '');
$telefono 			= request_var('telefono', '');
$email 				= request_var('email', '');
$padre 				= request_var('padre', '');
$madre 				= request_var('madre', '');
$grado 				= request_var('grado', 0);
$seccion 			= request_var('seccion', 0);

if (empty($nombre) || empty($apellido) || empty($carne)) {
	location('../alumnos/');
}

$sql_update = array(
	'codigo_alumno' => $codigo_alumno,
	'nombre_alumno' => $nombre,
	'apellido' => $apellido,
	'direccion' => $direccion,
	'telefono1' => $telefono,
	'email' => $email,
	'padre' => $padre,
	'madre' => $madre,
	'id_grado' => $id_grado
);
$sql = 'UPDATE alumno SET' . $db->sql_build('UPDATE', $sql_update) . sql_filter('
	WHERE id_alumno = ?
		AND carne = ?', $id_alumno, $carne);
$db->sql_query($sql);

$sql = 'SELECT *
	FROM reinscripcion
	WHERE id_alumno = ?
	ORDER BY anio DESC
	LIMIT 1';
if ($reinscripcion = $db->sql_fieldrow(sql_filter($sql, $id_alumno))) {
	$sql_update = [
		'id_grado' => $grado,
		'id_seccion' => $seccion
	];
	$sql = 'UPDATE reinscripcion SET ' . $db->sql_build('UPDATE', $sql_update) . sql_filter('
		WHERE id_alumno = ?
			AND anio = ?', $id_alumno, $reinscripcion->anio);
	$db->sql_query($sql);

	$sql_update = [
		'id_grado' => $grado
	];
	$sql = 'UPDATE notas SET ' . $db->sql_build('UPDATE', $sql_update) . sql_filter('
		WHERE id_alumno = ?
			AND id_grado = ?', $id_alumno, $reinscripcion->id_grado);
	$db->sql_query($sql);
}

location('../alumnos/');