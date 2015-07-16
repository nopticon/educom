<?php

require_once('../conexion.php');

$username = request_var('nombre', '');

// 
// Search for given firstname and lastname
// 
if ($username) {
	$username = simple_alias($username);

	$sql = 'SELECT a.id_alumno, a.carne, a.nombre_alumno, m.username_base
		FROM alumno a
		INNER JOIN _members m ON m.user_id = a.id_member
		WHERE m.username_base LIKE ?
		ORDER BY a.nombre_alumno';
	if ($alumnos = sql_rowset(sql_filter($sql, '%' . $username . '%'))) {
		foreach ($alumnos as $i => $row) {
			if (!$i) _style('results');

			$row->username_base = s_link('m', $row->username_base);

			_style('results.row', $row);
		}
	} else {
		_style('no_results');
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