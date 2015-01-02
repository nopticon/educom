<?php

require_once('../conexion.php');

$nombre = $_REQUEST['nombre'];
$apellido = $_REQUEST['apellido'];
$profesion = $_REQUEST['profesion'];
$email = $_REQUEST['email'];
$telefono = $_REQUEST['telefonos'];
$direccion = $_REQUEST['direccion'];
$observacion = $_REQUEST['observacion'];

$registro = "cmemou";
$status = "Alta";

$sql_insert = array(
	'registro' => $registro,
	'nombre_catedratico' => $nombre,
	'apellido' => $apellido,
	'profesion' => $profesion,
	'email' => $email,
	'telefono' => $telefono,
	'direccion' => $direccion,
	'observacion' => $observacion,
	'status' => $status
);
$sql = 'INSERT INTO catedratico' . $db->sql_build('INSERT', $sql_insert);
$db->sql_query($sql);

redirect('index.php');