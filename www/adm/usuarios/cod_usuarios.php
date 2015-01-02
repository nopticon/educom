<?php

require_once('../conexion.php');

$nombre = $_REQUEST['nombre'];
$usuario = $_REQUEST['usuario'];
$password = $_REQUEST['password'];

$sql_insert = array(
	'nombre' => $nombre,
	'usuario' => $usuario,
	'password' => $password
);
$sql = 'INSERT INTO usuarios' . $db->sql_build('INSERT', $sql_insert);
$db->sql_query($sql);

redirect('index.php');