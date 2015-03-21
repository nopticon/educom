<?php

require_once('../conexion.php');

$sql = 'SELECT nombre, status
	FROM grado g
	ORDER BY id_grado';
$list = $db->sql_rowset($sql);

foreach ($list as $i => $row) {
	if (!$i) _style('results');

	_style('results.row', $row);
}

$form = [[
	'grado' => [
		'type' => 'text',
		'value' => 'Nombre de grado'
	],
	'status' => [
		'type' => 'select',
		'show' => 'Estado',
		'value' => [
			'Alta' => 'Alta',
			'Baja' => 'Baja'
		]
	]
]];

_style('create', [
	'form' => build_form($form),
	'submit' => build_submit()
]);

page_layout('Grados', 'student_grades');