<?php

require_once('../conexion.php');

$year = date('Y');
$status = 'ReInscrito';

if (request_var('submit2', '')) {
	$id_alumno = request_var('id_alumno', 0);
	$carne = request_var('carnet', '');
	$id_grado = request_var('grado', 0);
	$id_seccion = request_var('seccion', 0);
	$encargado = request_var('encargado', '');
	$telefonos = request_var('telefonos', '');
	$observaciones = request_var('observacion', '');

	$sql_insert = [
		'id_alumno' => $id_alumno,
		'carne' => $carne,
		'id_grado' => $id_grado,
		'id_seccion' => $id_seccion,
		'observaciones' => $observaciones,
		'encargado_reinscripcion' => $encargado,
		'telefonos' => $telefonos,
		'status' => $status,
		'anio' => $anio
	];
	$sql = 'INSERT INTO reinscripcion' . $db->sql_build('INSERT', $sql_insert);
	$db->sql_query($sql);

	location('.');
}

if (request_var('submit', '')) {
	$carne = request_var('carne', '');

	$sql = 'SELECT *
		FROM alumno a, reinscripcion r, grado g
		WHERE r.id_alumno = a.id_alumno
			AND g.id_grado = r.id_grado
			AND a.carne = ?
		ORDER BY a.id_alumno DESC';
	if (!$row = $db->sql_fieldrow(sql_filter($sql, $carne))) {
		location('.');
	}

	//
	// Database
	//
	$sql = 'SELECT id_grado
		FROM reinscripcion
		WHERE carne = ?
		ORDER BY id_grado DESC
		LIMIT 1';
	$last_grade = $db->sql_field(sql_filter($sql, $carne), 'id_grado');

	$sql = 'SELECT *
		FROM grado
		WHERE status = ?
			AND id_grado > ?';
	if (!$rowset_grado = $db->sql_rowset(sql_filter($sql, 'Alta', $last_grade))) {
		$rowset_grado = [];
	}

	$primer_seccion = (isset($rowset_grado[0]->id_grado)) ? $rowset_grado[0]->id_grado : 0;

	$sql = 'SELECT *
		FROM secciones
		WHERE id_grado = ?';
	if (!$rowset_seccion = $db->sql_rowset(sql_filter($sql, $primer_seccion))) {
		$rowset_seccion = [];
	}

	//
	// Historial de grados
	//
	$sql = 'SELECT *
		FROM reinscripcion r, alumno a, grado g, secciones s
		WHERE r.id_alumno = a.id_alumno
			AND r.id_grado = g.id_grado
			AND s.id_seccion = r.id_seccion
			AND s.id_grado = g.id_grado
			AND r.carne = ?';
	$rowset_historia = $db->sql_rowset(sql_filter($sql, $carne));

	$form = [
		'Datos de Alumno' => [
			'carne' => [
				'type' => 'text',
				'value' => 'Carn&eacute;',
				'default' => $row->carne
			],
			'codigo_alumno' => [
				'type' => 'text',
				'value' => 'C&oacute;digo de alumno',
				'default' => $row->codigo_alumno
			],
			'nombre' => [
				'type' => 'text',
				'value' => 'Nombre',
				'default' => $row->nombre_alumno
			],
			'apellido' => [
				'type' => 'text',
				'value' => 'Apellido',
				'default' => $row->apellido
			]
		],
		'Datos de Padres' => [
			'padre' => [
				'type' => 'text',
				'value' => 'Padre',
				'default' => $row->padre
			],
			'madre' => [
				'type' => 'text',
				'value' => 'Madre',
				'default' => $row->madre
			]
		],
		'Datos de Encargado ' . $year => [
			'Encargado' => [
				'type' => 'text',
				'value' => 'Encargado'
			],
			'telefonos' => [
				'type' => 'text',
				'value' => 'Tel&eacute;fonos'
			],
			'observacion' => [
				'type' => 'text',
				'value' => 'Observaciones'
			]
		],
		'Grado a Cursar' => [
			'grado' => [
				'type' => 'select',
				'show' => 'Grado',
				'value' => []
			],
			'seccion' => [
				'type' => 'select',
				'show' => 'Secci&oacute;n',
				'value' => []
			]
		]
	];

	foreach ($rowset_grado as $row) {
		$form['Grado a Cursar']['grado']['value'][$row->id_grado] = $row->nombre;
	}

	foreach ($rowset_seccion as $row) {
		$form['Grado a Cursar']['seccion']['value'][$row->id_seccion] = $row->nombre_seccion;
	}

	if (!count($form['Grado a Cursar']['grado']['value'])) {
		$form['Grado a Cursar'] = [];
	}

	if (count($form['Grado a Cursar'])) {
		_style('create_results', [
			'id_alumno' => $row->id_alumno,
			'carne' => $row->carne,

			'form' => build_form($form),
			'submit' => build_submit()
		]);
	} else {
		_style('no_create');
	}

	foreach ($rowset_historia as $i => $row) {
		if (!$i) _style('results');

		_style('results.row', $row);
	}
} else {
	$sql = 'SELECT r.fecha_reinscripcion, a.carne, a.nombre_alumno, a.apellido, g.nombre, s.nombre_seccion, a.sexo
		FROM reinscripcion r, alumno a, grado g, secciones s
		WHERE r.id_alumno = a.id_alumno
			AND r.id_grado = g.id_grado
			AND s.id_seccion = r.id_seccion
			AND anio = ?
			AND r.status = ?
		ORDER BY r.id_reinscripcion DESC LIMIT 50';
	$list = $db->sql_rowset(sql_filter($sql, $year, $status));

	foreach ($list as $i => $row) {
		if (!$i) _style('results');

		_style('results.row', $row);
	}

	$form = [[
		'carne' => [
			'type' => 'input',
			'value' => 'Carn&eacute;'
		]
	]];

	_style('create', [
		'form' => build_form($form),
		'submit' => build_submit()
	]);
}

page_layout('Registro de alumnos', 'student_registration');