<?php

require_once('../conexion.php');

$curso = $_REQUEST['curso'];
$examen = $_REQUEST['examen'];
$grado = $_REQUEST['grado'];
$nota = $_REQUEST['nota'];

foreach ($nota as $alumno => $valor) {
	$valor = (int) $valor;

	if (!$valor) continue;

	$sql_insert = array(
		'id_alumno' => $alumno,
		'id_grado' => $grado,
		'id_curso' => $curso,
		'id_bimestre' => $examen,
		'nota' => $valor,
	);
	$sql = 'INSERT INTO notas' . $db->sql_build('INSERT', $sql_insert);
	$db->sql_query($sql);
}

redirect('index.php');