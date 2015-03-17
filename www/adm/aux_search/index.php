<?php

require_once('../conexion.php');

$nombre = request_var('nombre', '');
$apellido = request_var('apellido', '');

// 
// Search for given firstname and lastname
// 
if ($nombre || $apellido) {
	$sql = 'SELECT id_alumno, carne, apellido, nombre_alumno
		FROM alumno a
		INNER JOIN _members m ON m.user_id = a.id_member
		WHERE m.username_base LIKE ?
			AND m.username_base LIKE ?
		ORDER BY a.apellido, a.nombre_alumno';
	if ($alumnos = sql_rowset(sql_filter($sql, '%' . $nombre . '%', '%' . $apellido . '%'))) {
		foreach ($alumnos as $i => $row) {
			if (!$i) _style('results');

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
		'value' => 'Nombres'
	],
	'apellido' => [
		'type' => 'input',
		'value' => 'Apellido'
	]
]];

_style('search_student', [
	'form' => build_form($form),
	'submit' => build_submit()
]);

page_layout('Busqueda de Alumno', 'student_search');