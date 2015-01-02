<?php
require_once('../conexion.php');

$grado = $_REQUEST['grado'];
$seccion = $_REQUEST['seccion'];

$sql_insert = array(
	'id_grado' => $grado,
	'nombre_seccion' => $seccion
);
$sql = 'INSERT INTO secciones' . $db->sql_build('INSERT', $sql_insert);
$db->sql_query($sql);

redirect('index.php');