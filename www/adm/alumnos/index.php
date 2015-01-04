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
		'CodigoAlumno' => array(
			'type' => 'text',
			'value' => 'C&oacute;digo de alumno'
		),
		'Nombre' => array(
			'type' => 'text',
			'value' => 'Nombre'
		),
		'Apellido' => array(
			'type' => 'text',
			'value' => 'Apellido'
		),
		'Direccion' => array(
			'type' => 'text',
			'value' => 'Direcci&oacute;n'
		),
		'Telefono' => array(
			'type' => 'text',
			'value' => 'Tel&eacute;fono'
		),
		'Edad' => array(
			'type' => 'text',
			'value' => 'Edad'
		),
		'Email' => array(
			'type' => 'text',
			'value' => 'Email'
		),
		'Sexo' => array(
			'type' => 'radio',
			'value' => array(
				'M' => 'Masculino',
				'F' => 'Femenino'
			)
		),
	),
	'Datos de Padres' => array(
		'Padre' => array(
			'type' => 'text',
			'value' => 'Padre'
		),
		'Madre' => array(
			'type' => 'text',
			'value' => 'Madre'
		),
	),
	'Datos de Encargado' => array(
		'Encargado' => array(
			'type' => 'text',
			'value' => 'Encargado'
		),
		'Profesion' => array(
			'type' => 'text',
			'value' => 'Profesi&oacute;n o oficio'
		),
		'Labor' => array(
			'type' => 'text',
			'value' => 'Lugar de trabajo'
		),
		'Direccion2' => array(
			'type' => 'text',
			'value' => 'Direcci&oacute;n'
		),
		'DPI' => array(
			'type' => 'text',
			'value' => 'DPI'
		),
		'Extendido' => array(
			'type' => 'text',
			'value' => 'Extendido'
		),
	),
	'En caso de emergencia' => array(
		'Emergencia' => array(
			'type' => 'radio',
			'value' => array(
				'Padre' => 'Padre',
				'Madre' => 'Madre',
				'Encargado' => 'Encargado',
			)
		),
		'Telefono2' => array(
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

<script type="text/javascript">
//<![CDATA[
$(function() {
	$('#inputgrado').change(function() {
		$.ajax({
			type: "POST",
			url: "../actseccion.php",
			data: "grado=" + $(this).val(),
			success: function(msg) {
				$('#inputseccion').html(msg);
			}
		});
	});
});
//]]>
</script>

<?php pie(); ?>