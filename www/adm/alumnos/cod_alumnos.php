<?php

require_once("../conexion.php");

$codigo = request_var('codigo_alumno', '');
$nombre = request_var('nombre', '');
$apellido = request_var('apellido', '');
$direccion = request_var('direccion', '');
$orden = request_var('orden', '');
$registro = request_var('registro', '');
$telefono1 = request_var('telefono', '');
$edad = request_var('edad', '');
$sexo = request_var('sexo', '');
$email = request_var('email', '');

$padre = request_var('padre', '');
$madre = request_var('madre', '');

$encargado = request_var('encargado', '');
$profesion = request_var('profesion', '');
$labor = request_var('labor', '');
$direccion_labora = request_var('direccion2', '');
$encargado_email = request_var('email_encargado', '');
$dpi = request_var('dpi', '');
$extendido = request_var('extendido', '');

$emergencia = request_var('emergencia', '');
$telefono2 = request_var('telefono2', '');

$grado = request_var('grado', 0);
$seccion = request_var('seccion', 0);

$status = 'Inscrito';
$anio = date('Y');
$carne = $anio . $sexo;

$insert_alumno = array(
	'carne' => $carne,
	'codigo_alumno' => $codigo,
	'nombre_alumno' => $nombre,
	'apellido' => $apellido,
	'direccion' => $direccion,
	'orden' => '',
	'registro' => '',
	'telefono1' => $telefono1,
	'edad' => $edad,
	'sexo' => $sexo,
	'email' => $email,
	'padre' => $padre,
	'madre' => $madre,
	'encargado' => $encargado,
	'profesion' => $profesion,
	'labora' => $labor,
	'direccion_labora' => $direccion_labora,
	'encargado_email' => $encargado_email,
	'dpi' => $dpi,
	'extendida' => $extendido,
	'emergencia' => $emergencia,
	'telefono2' => $telefono2,
	'status' => $status
);

_pre($insert_alumno, true);

$sql = 'INSERT INTO alumno' . $db->sql_build('INSERT', $insert_alumno);
$db->sql_query($sql);

$sql = 'SELECT id_alumno
	FROM alumno
	WHERE carne = ?';
$id = $db->sql_field($db->__prepare($sql, $carne), 'id_alumno');

// Add $id to carne
$carne .= $id;

$insert_inscripcion = array(
	'id_alumno' => $id,
	'carne' => $carne,
	'id_grado' => $grado,
	'id_seccion' => $seccion,
	'encargado_reinscripcion' => $encargado,
	'telefonos' => $telefono2,
	'status' => $status,
	'anio' => $anio
);

$sql = 'INSERT INTO reinscripcion' . $db->sql_build('INSERT', $insert_inscripcion);
$db->sql_query($sql);

$update_alumno = array(
	'carne' => $carne
);
$sql = 'UPDATE alumno' . $db->sql_build('UPDATE', $update_alumno) . $db->__prepare('
	WHERE id_alumno = ?', $id);
$db->sql_query($sql);

redirect('/adm/alumnos/index2.php');