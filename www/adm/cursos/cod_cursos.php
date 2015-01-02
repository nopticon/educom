<?php

require_once('../conexion.php');

$curso = $_REQUEST['curso'];
$capacidad = $_REQUEST['capacidad'];
$grado = $_REQUEST['grado'];
$catedratico = $_REQUEST['catedratico'];
$areas_cursos = $_REQUEST['areas_cursos'];
$status = 'Alta';

$sql_insert = array(
	'id_area' => $areas_cursos,
	'nombre_curso' => $curso,
	'capacidad' => $capacidad,
	'status' => $status,
	'id_grado' => $grado,
	'id_catedratico' => $catedratico,
);
$sql = 'INSERT INTO cursos' . $db->sql_build('INSERT', $sql_insert);
$db->sql_query($sql);

redirect('index.php');