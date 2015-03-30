<?php

require_once('../../conexion.php');

$id_catedratico = request_var('id_catedratico', 0);

$nombre 		= request_var('nombre', '');
$apellido 		= request_var('apellido', '');
$profesion 		= request_var('profesion', '');
$email 			= request_var('email', '');
$telefono 		= request_var('telefonos', '');
$direccion 		= request_var('direccion', '');
$observacion 	= request_var('observacion', '');

if (empty($nombre) || empty($apellido)) {
	location('../../catedraticos/');
}

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

location('../../catedraticos/');