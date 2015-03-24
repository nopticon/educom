<?php

require_once('../conexion.php');

if (request_var('submit2', '')) {
	$curso = request_var('curso', 0);
	$examen = request_var('examen', 0);
	$grado = request_var('grado', 0);
	$scores = request_var('nota', [0 => 0]);

	foreach ($scores as $student => $score) {
		if (empty($score)) continue;

		$sql_insert = array(
			'id_alumno' => $student,
			'id_grado' => $grado,
			'id_curso' => $curso,
			'id_bimestre' => $examen,
			'nota' => $score,
		);
		$sql = 'INSERT INTO notas' . $db->sql_build('INSERT', $sql_insert);
		$db->sql_query($sql);
	}

	location('.');
}

if (request_var('submit', '')) {
	$seccion = $_REQUEST['grado'];
	$curso = $_REQUEST['curso'];
	$examen = $_REQUEST['examen'];
	$anio = $_REQUEST['anio'];

	$sql = 'SELECT id_grado, nombre_seccion
		FROM secciones
		WHERE id_seccion = ?';
	if (!$gradoar = $db->sql_fieldrow(sql_filter($sql, $seccion))) {
		// location('.');
	}

	$grado = $gradoar->id_grado;

	$sql = 'SELECT *
		FROM grado
		WHERE id_grado = ?';
	if (!$_grado = $db->sql_fieldrow(sql_filter($sql, $grado))) {
		// location('.');
	}

	$sql = 'SELECT *
		FROM cursos
		WHERE id_curso = ?';
	if (!$_curso = $db->sql_fieldrow(sql_filter($sql, $curso))) {
		// location('.');
	}

	$sql = 'SELECT *
		FROM examenes
		WHERE id_examen = ?';
	if (!$_examen = $db->sql_fieldrow(sql_filter($sql, $examen))) {
		// location('.');
	}

	$sql = 'SELECT *
		FROM alumno a, grado g, reinscripcion r
		WHERE g.id_grado = ?
			AND r.id_seccion = ?
			AND r.anio = ?
			AND r.id_grado = g.id_grado
			AND r.id_alumno = a.id_alumno
		ORDER BY a.apellido ASC';
	if (!$list = $db->sql_rowset(sql_filter($sql, $grado, $seccion, $anio))) {
		// location('.');
	}

	foreach ($list as $i => $row) {
		if (!$i) _style('results', [
			'GRADE_ID' => $grado,
			'COURSE_ID' => $curso,
			'UNIT_ID' => $_examen->id_examen,
			'GRADE_NAME' => $_grado->nombre,
			'GRADE_SECTION' => $gradoar->nombre_seccion,
			'COURSE_NAME' => $_curso->nombre_curso,
			'UNIT_NAME' => $_examen->examen,
			'YEAR' => $anio
		]);

		$sql = 'SELECT *
			FROM notas
			WHERE id_alumno = ?
				AND id_grado = ?
				AND id_curso = ?
				AND id_bimestre = ?';
		$row->score = $db->sql_field(sql_filter($sql, $row->id_alumno, $grado, $curso, $examen), 'nota', '');

		_style('results.row', $row);
	}
} else {
	$sql = "SELECT id_seccion, nombre, nombre_seccion
		FROM grado g, secciones s
		WHERE g.id_grado = s.id_grado
			AND status = 'Alta'";
	$grado = $db->sql_rowset($sql);

	$sql = 'SELECT id_curso, nombre_curso
		FROM cursos
		WHERE id_grado = 1';
	$cursos = $db->sql_rowset($sql);

	$sql = 'SELECT id_examen, examen
		FROM examenes';
	$examenes = $db->sql_rowset($sql);

	$form = [[
		'grado' => [
			'type' => 'select',
			'show' => 'Grado',
			'value' => []
		],
		'curso' => [
			'type' => 'select',
			'show' => 'Curso',
			'value' => []
		],
		'examen' => [
			'type' => 'select',
			'show' => 'Examen',
			'value' => []
		],
		'anio' => [
			'type' => 'select',
			'show' => 'A&ntilde;o',
			'value' => '*'
		]
	]];

	foreach ($grado as $row) {
		$form[0]['grado']['value'][$row->id_seccion] = $row->nombre . ' - ' . $row->nombre_seccion;
	}

	foreach ($cursos as $row) {
		$form[0]['curso']['value'][$row->id_curso] = $row->nombre_curso;
	}

	foreach ($examenes as $row) {
		$form[0]['examen']['value'][$row->id_examen] = $row->examen;
	}

	_style('create', [
		'form' => build_form($form),
		'submit' => build_submit()
	]);
}

page_layout('Notas', 'student_scores');