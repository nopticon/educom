<?php

require_once("../conexion.php");

$codigo = $_REQUEST['codigo_alumno'];
$nombre = $_REQUEST['nombre'];
$apellido = $_REQUEST['apellido'];
$direccion = $_REQUEST['direccion'];
$orden = $_REQUEST['orden'];
$registro = $_REQUEST['registro'];
$telefono1 = $_REQUEST['telefono1'];
$edad = $_REQUEST['edad'];
$sexo = $_REQUEST['sexo'];
$email = $_REQUEST['email'];

$padre = $_REQUEST['padre'];
$madre = $_REQUEST['madre'];

$encargado = $_REQUEST['encargado'];
$profesion = $_REQUEST['profesion'];
$laborando = $_REQUEST['labor'];
$direccion_labora = $_REQUEST['direccion2'];
$dpi = $_REQUEST['dpi'];
$extendido = $_REQUEST['extendido'];

$emergencia = $_REQUEST['emergencia'];
$telefono2 = $_REQUEST['telefono2'];

$status = "Inscrito";

$grado = $_REQUEST['grado'];
$seccion = $_REQUEST['seccion'];

$anio = date('Y');
$carne = $anio . $sexo;

$insert_alumno = array(
	'carne' => $carne,
	'codigo_alumno' => $codigo,
	'nombre_alumno' => $nombre,
	'apellido' => $apellido,
	'direccion' => $direccion,
	'orden' => $orden,
	'registro' => $registro,
	'telefono1' => $telefono1,
	'edad' => $edad,
	'sexo' => $sexo,
	'email' => $email,
	'padre' => $padre,
	'madre' => $madre,
	'encargado' => $encargado,
	'profesion' => $profesion,
	'labora' => $labora,
	'direccion_labora' => $direccion_labora,
	'dpi' => $dpi,
	'extendida' => $extendido,
	'emergencia' => $emergencia,
	'telefono2' => $telefono2,
	'status' => $status
);

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

redirect('index2.php');