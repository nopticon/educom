<?php

require_once('../conexion.php');

$curso = $_REQUEST['curso'];
$examen = $_REQUEST['examen'];
$grado = $_REQUEST['grado'];
$nota = $_REQUEST['nota'];

foreach ($nota as $alumno => $nota) {
	$sql = 'SELECT *
		FROM notas
		WHERE id_alumno = ?
			AND id_grado = ' . (int) $grado . '
			AND id_curso = ' . (int) $curso . '
			AND id_bimestre = ' . (int) $examen;
	if ($cada_nota = $db->sql_fieldrow($db->__prepare($sql, $alumno, $grado, $curso, $examen)) {
		if (!$nota) {
			$sql = 'DELETE FROM notas
				WHERE id_nota = ?';
			$db->sql_query($db->__prepare($sql, $cada_nota['id_nota']));
		} else {
			$sql = 'UPDATE notas SET nota = ?
				WHERE id_nota = ?';
			$db->sql_query($db->__prepare($sql, $nota, $cada_nota['id_nota']));
		}

		continue;
	}

	if (!$nota) continue;

	$sql_insert = array(
		'id_alumno' => $alumno,
		'id_grado' => $grado,
		'id_curso' => $curso,
		'id_bimestre' => $examen,
		'nota' => $nota,
	);
	$sql = 'INSERT INTO notas' . $db->sql_build('INSERT', $sql_insert);
	$db->sql_query($sql);
}

redirect('index.php');