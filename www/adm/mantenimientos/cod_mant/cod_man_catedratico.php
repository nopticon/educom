<?php

require_once('../../conexion.php');

$id_catedratico = $_REQUEST['id_catedratico'];

$nombre = $_REQUEST['nombre'];
$apellido = $_REQUEST['apellido'];
$profesion = $_REQUEST['profesion'];
$email = $_REQUEST['email'];
$telefono = $_REQUEST['telefonos'];
$direccion = $_REQUEST['direccion'];
$observacion = $_REQUEST['observacion'];

$sql_update = array(
	'nombre_catedratico' => $nombre,
	'apellido' => $apellido,
	'profesion' => $profesion,
	'email' => $email,
	'telefono' => $telefono,
	'direccion' => $direccion,
	'observacion' => $observacion
);
$sql = 'UPDATE catedratico SET ' . $db->sql_build('UPDATE', $sql_update) . sql_filter('
	WHERE id_catedratico = ?', $id_catedratico);
$db->sql_query($sql);

redirect('../catedraticos/index.php');