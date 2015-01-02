<?php

require_once('../../conexion.php');

$id_alumno = $_REQUEST['id_alumno'];
$carne = $_REQUEST['carnet'];

$codigo_alumno = $_REQUEST['codigo_alumno'];

$nombre = $_REQUEST['nombre'];
$apellido = $_REQUEST['apellido'];

$direccion = $_REQUEST['direccion'];
$telefono = $_REQUEST['telefono'];
$email = $_REQUEST['email'];

$padre = $_REQUEST['padre'];
$madre = $_REQUEST['madre'];

$grado = $_REQUEST['grado'];
$seccion = $_REQUEST['seccion'];

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
$sql = 'UPDATE alumno SET' . $db->sql_build('UPDATE', $sql_update) . $db->__prepare('
	WHERE id_alumno = ?
		AND carne = ?', $id_alumno, $carne);
$db->sql_query($sql);

$sql = 'SELECT *
	FROM reinscripcion
	WHERE id_alumno = ?
	ORDER BY anio DESC
	LIMIT 1';
if ($reinscripcion = $db->sql_fieldrow($db->__prepare($sql, $id_alumno))) {
	$sql_update = array(
		'id_grado' => $grado,
		'id_seccion' => $seccion
	);
	$sql = 'UPDATE reinscripcion SET ' . $db->sql_build('UPDATE', $sql_update) . $db->__prepare('
		WHERE id_alumno = ?
			AND anio = ?', $id_alumno, $reinscripcion->anio);
	$db->sql_query($sql);

	$sql_update = array(
		'id_grado' => $grado
	);
	$sql = 'UPDATE notas SET ' . $db->sql_build('UPDATE', $sql_update) . $db->__prepare('
		WHERE id_alumno = ?
			AND id_grado = ?', $id_alumno, $reinscripcion->id_grado);
	$db->sql_query($sql);
}

redirect('../alumnos/index.php');