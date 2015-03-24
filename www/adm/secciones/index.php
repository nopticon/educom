<?php

require_once('../conexion.php');

if (request_var('submit', '')) {
	$grado = request_var('grado', 0);
	$seccion = request_var('seccion', '');

	if (empty($seccion)) {
		location('.');
	}

	$sql_insert = [
		'id_grado' => $grado,
		'nombre_seccion' => $seccion
	];
	$sql = 'INSERT INTO secciones' . $db->sql_build('INSERT', $sql_insert);
	$db->sql_query($sql);

	location('.');
}

$sql = "SELECT *
	FROM grado
	WHERE status = 'Alta'";
$grado = $db->sql_rowset($sql);

$sql = "SELECT nombre, nombre_seccion
	FROM secciones s, grado g
	WHERE g.id_grado = s.id_grado
		AND g.status = 'Alta'";
$secciones = $db->sql_rowset($sql);

foreach ($secciones as $i => $row) {
	if (!$i) _style('results');

	_style('results.row', $row);
}

$form = [[
	'grado' => [
		'type' => 'select',
		'show' => 'Grado',
		'value' => []
	],
	'seccion' => [
		'type' => 'text',
		'value' => 'Secci&oacute;n'
	]
]];

foreach ($grado as $row) {
	$form[0]['grado']['value'][$row->id_grado] = $row->nombre;
}

_style('create', [
	'form' => build_form($form),
	'submit' => build_submit()
]);

page_layout('Secciones', 'student_sections');