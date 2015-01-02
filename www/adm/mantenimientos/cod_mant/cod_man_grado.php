<?php

require_once('../../conexion.php');

$id_grado = $_REQUEST['id_grado'];

$nombre = $_REQUEST['grado'];
$status = $_REQUEST['status'];

$sql_update = array(
	'nombre' => $nombre,
	'status' => $status,
);
$sql = 'UPDATE grado SET' . $db->sql_build('UPDATE', $sql_update) . $db->__prepare('
	WHERE id_grado = ?', $id_grado);
$db->sql_query($sql);

redirect('../grados/index.php');