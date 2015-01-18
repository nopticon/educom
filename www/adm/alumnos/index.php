<?php

require_once('../conexion.php');

encabezado('Crear alumno');

$sql = 'SELECT *
	FROM grado
	WHERE status = ?';
$grado = $db->sql_rowset($db->__prepare($sql, 'Alta'));

$sql = 'SELECT *
	FROM secciones
	WHERE id_grado = 1';
$seccion = $db->sql_rowset($sql);

//
// Create fields
//
$form = array(
	'Datos de Alumno' => array(
		'codigo_alumno' => array(
			'type' => 'text',
			'value' => 'C&oacute;digo de alumno'
		),
		'nombre' => array(
			'type' => 'text',
			'value' => 'Nombre'
		),
		'apellido' => array(
			'type' => 'text',
			'value' => 'Apellido'
		),
		'direccion' => array(
			'type' => 'text',
			'value' => 'Direcci&oacute;n'
		),
		'telefono' => array(
			'type' => 'text',
			'value' => 'Tel&eacute;fono'
		),
		'edad' => array(
			'type' => 'text',
			'value' => 'Edad'
		),
		'email' => array(
			'type' => 'text',
			'value' => 'Email'
		),
		'sexo' => array(
			'show' => 'Sexo',
			'type' => 'select',
			'value' => array(
				'M' => 'Masculino',
				'F' => 'Femenino'
			)
		),
	),
	'Datos de Padres' => array(
		'padre' => array(
			'type' => 'text',
			'value' => 'Padre'
		),
		'madre' => array(
			'type' => 'text',
			'value' => 'Madre'
		),
	),
	'Datos de Encargado' => array(
		'encargado' => array(
			'type' => 'text',
			'value' => 'Encargado'
		),
		'profesion' => array(
			'type' => 'text',
			'value' => 'Profesi&oacute;n o oficio'
		),
		'labor' => array(
			'type' => 'text',
			'value' => 'Lugar de trabajo'
		),
		'email_encargado' => array(
			'type' => 'text',
			'value' => 'Email'
		),
		'direccion2' => array(
			'type' => 'text',
			'value' => 'Direcci&oacute;n'
		),
		'dpi' => array(
			'type' => 'text',
			'value' => 'DPI'
		),
		'extendido' => array(
			'type' => 'text',
			'value' => 'Extendido'
		),
	),
	'En caso de emergencia' => array(
		'emergencia' => array(
			'show' => 'Llamar a',
			'type' => 'select',
			'value' => array(
				'Encargado' => 'Encargado',
				'Padre' => 'Padre',
				'Madre' => 'Madre',
			)
		),
		'telefono2' => array(
			'type' => 'text',
			'value' => 'Tel&eacute;fonos'
		),
	),
	'Inscripci&oacute;n ' . date('Y') => array(
		'grado' => array(
			'type' => 'select',
			'show' => 'Grado',
			'value' => array()
		),
		'seccion' => array(
			'type' => 'select',
			'show' => 'Secci&oacute;n',
			'value' => array()
		)
	)
);

foreach ($grado as $row) {
	$form['Inscripci&oacute;n ' . date('Y')]['grado']['value'][$row->id_grado] = $row->nombre;
}

foreach ($seccion as $row) {
	$form['Inscripci&oacute;n ' . date('Y')]['seccion']['value'][$row->id_seccion] = $row->nombre_seccion;
}

?>

<form class="form-horizontal" action="cod_alumnos.php" method="post">
	<?php build($form); submit('Crear alumno'); ?>
</form>

<?php pie(); ?>