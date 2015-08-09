<?php

require_once('../conexion.php');

$username = request_var('nombre', '');
$alpha = request_var('a', '');

// 
// Search for given firstname and lastname
// 
if ($username || $alpha) {
	if ($username) {
		$username = simple_alias($username);

		$sql = 'SELECT a.id_alumno, a.carne, a.nombre_alumno, m.username_base
			FROM alumno a
			INNER JOIN _members m ON m.user_id = a.id_member
			INNER JOIN reinscripcion r ON a.id_alumno = r.id_alumno
			WHERE m.username_base LIKE ?
				AND r.anio = ?
			ORDER BY a.nombre_alumno';
		$alumnos = sql_rowset(sql_filter($sql, '%' . $username . '%', date('Y')));

		$block = 'results';
	} elseif ($alpha) {
		$sql = 'SELECT a.id_alumno, a.carne, a.nombre_alumno, m.username_base
			FROM alumno a
			INNER JOIN _members m ON m.user_id = a.id_member
			INNER JOIN reinscripcion r ON a.id_alumno = r.id_alumno
			WHERE m.username_base LIKE ?
				AND r.anio = ?
			ORDER BY a.nombre_alumno';
		// _pre(sql_filter($sql, $alpha . '%', date('Y')), true);
		$alumnos = sql_rowset(sql_filter($sql, $alpha . '%', date('Y')));

		$block = 'results_alpha';
	}

	if ($alumnos) {
		foreach ($alumnos as $i => $row) {
			if (!$i) _style($block);

			$row->username_base = s_link('m', $row->username_base);

			_style([$block, 'row'], $row);
		}
	} else {
		_style('no_' . $block);
	}
}

// 
// Get alpha list
// 
$sql = 'SELECT *
	FROM alumno a
	ORDER BY a.nombre_alumno';
$list_alpha = sql_rowset($sql);

$alpha = array();
foreach ($list_alpha as $i => $row) {
	$alpha[substr($row->nombre_alumno, 0, 1)] = 1;
}

$alpha = array_keys($alpha);
foreach ($alpha as $i => $row) {
	if (!$i) _style('alpha');

	_style('alpha.row', [
		'URL' => '?a=' . strtolower($row),
		'LETTER' => $row
	]);
}

$sql = 'SELECT a.nombre_alumno, m.username_base
	FROM alumno a
	INNER JOIN _members m ON m.user_id = a.id_member
	INNER JOIN reinscripcion r ON r.id_alumno = a.id_alumno
	INNER JOIN secciones s ON r.id_seccion = s.id_seccion
	WHERE r.id_seccion IN (
		SELECT r2.id_seccion
		FROM alumno a2
		INNER JOIN reinscripcion r2 ON r2.id_alumno = a2.id_alumno
		WHERE a2.id_member = ?
			AND r2.anio = ?
	)
	AND a.id_member <> ?
	ORDER BY a.nombre_alumno';
if ($classmates = sql_rowset(sql_filter($sql, $user->d('user_id'), date('Y'), $user->d('user_id')))) {
	foreach ($classmates as $i => $row) {
		if (!$i) _style('classmates');

		$row->username_base = s_link('m', $row->username_base);	

		_style('classmates.row', $row);
	}
}

// 
// Create form
// 
$form = [[
	'nombre' => [
		'type' => 'input',
		'value' => 'Nombre'
	]
]];

_style('search_student', [
	'form' => build_form($form),
	'submit' => build_submit()
]);

page_layout('Busqueda de Alumno', 'student_search_profile');