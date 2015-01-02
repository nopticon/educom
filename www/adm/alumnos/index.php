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
		'OrdenCedula' => array(
			'type' => 'select',
			'show' => 'Orden C&eacute;dula',
			'value' => array(
				'A01' => 'A01',
				'B02' => 'B02',
				'C03' => 'C03',
				'D04' => 'D04',
				'E05' => 'E05',
				'F06' => 'F06',
				'G07' => 'G07',
				'H08' => 'H08',
				'I09' => 'I09',
				'J10' => 'J10',
				'K11' => 'K11',
				'L12' => 'L12',
				'M13' => 'M13',
				'N14' => 'N14',
				'&Ntilde;15' => '&Ntilde;15',
				'016' => 'O16',
				'P17' => 'P17',
				'Q18' => 'Q18',
				'R19' => 'R19',
				'S20' => 'S20',
				'T21' => 'T21',
				'U22' => 'U22'
			)
		),
		'Registro' => array(
			'type' => 'text',
			'value' => 'Registro C&eacute;dula'
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