<?php

require_once('../../conexion.php');

$id_curso = $_REQUEST['id_curso'];
$id_grado = $_REQUEST['id_grado'];

$curso = $_REQUEST['curso'];
$capacidad = $_REQUEST['capacidad'];
$status = $_REQUEST['status'];

$sql_update = array(
	'nombre_curso' => $curso,
	'capacidad' => $capacidad,
	'status' => $status
);
$sql = 'UPDATE cursos SET' . $db->sql_build('UPDATE', $sql_update) . $db->__prepare('
	WHERE id_curso = ?', $id_curso);
$db->sql_query($sql);

redirect('../cursos/cursos.php?grado=' . $id_grado);