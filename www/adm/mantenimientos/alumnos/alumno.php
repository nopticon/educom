<?php

require_once('../../conexion.php');

$carne = $_REQUEST['carne'];

$sql = 'SELECT *
	FROM alumno
	WHERE carne = ?';
if (!$alumno = $db->sql_fieldrow($db->__prepare($sql, $carne))) {
	redirect('index.php');
}

encabezado('Mantenimiento Alumno', '../');

$form = array(
	'Datos de Alumno' => array(
		'CodigoAlumno' => array(
			'type' => 'text',
			'value' => 'C&oacute;digo de alumno',
			'default' => $alumno->codigo_alumno
		),
		'Carne' => array(
			'type' => 'text',
			'value' => 'Carn&eacute;',
			'default' => $alumno->carne
		),
		'Nombre' => array(
			'type' => 'text',
			'value' => 'Nombre',
			'default' => $alumno->nombre_alumno
		),
		'Apellido' => array(
			'type' => 'text',
			'value' => 'Apellido',
			'default' => $alumno->apellido
		),
		'Direccion' => array(
			'type' => 'text',
			'value' => 'Direcci&oacute;n',
			'default' => $alumno->direccion
		),
		'Telefono' => array(
			'type' => 'text',
			'value' => 'Tel&eacute;fono',
			'default' => $alumno->telefono1
		),
		'Edad' => array(
			'type' => 'text',
			'value' => 'Edad',
			'default' => $alumno->edad
		),
		'Email' => array(
			'type' => 'text',
			'value' => 'Email',
			'default' => $alumno->email
		),
		'Sexo' => array(
			'type' => 'radio',
			'value' => array(
				'M' => 'Masculino',
				'F' => 'Femenino'
			),
			'default' => $alumno->sexo
		),
	),
	'Datos de Padres' => array(
		'Padre' => array(
			'type' => 'text',
			'value' => 'Padre',
			'default' => $alumno->padre
		),
		'Madre' => array(
			'type' => 'text',
			'value' => 'Madre',
			'default' => $alumno->madre
		),
	),
	'Datos de Encargado' => array(
		'Encargado' => array(
			'type' => 'text',
			'value' => 'Encargado',
			'default' => $alumno->encargado
		),
		'Profesion' => array(
			'type' => 'text',
			'value' => 'Profesi&oacute;n o oficio',
			'default' => $alumno->profesion
		),
		'Labor' => array(
			'type' => 'text',
			'value' => 'Lugar de trabajo',
			'default' => $alumno->labora
		),
		'Direccion2' => array(
			'type' => 'text',
			'value' => 'Direcci&oacute;n',
			'default' => $alumno->direccion_labora
		),
		'orden' => array(
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
			),
			'default' => $alumno->orden
		),
		'Registro' => array(
			'type' => 'text',
			'value' => 'Registro C&eacute;dula',
			'default' => $alumno->registro
		),
		'DPI' => array(
			'type' => 'text',
			'value' => 'DPI',
			'default' => $alumno->dpi
		),
		'Extendida' => array(
			'type' => 'text',
			'value' => 'Extendido',
			'default' => $alumno->extendida
		),
	),
	'En caso de emergencia' => array(
		'Emergencia' => array(
			'type' => 'radio',
			'value' => array(
				'Padre' => 'Padre',
				'Madre' => 'Madre',
				'Encargado' => 'Encargado',
			),
			'default' => $alumno->emergencia
		),
		'Telefono2' => array(
			'type' => 'text',
			'value' => 'Tel&eacute;fonos',
			'default' => $alumno->telefono2
		),
	)
);

?>

<br />
<form class="form-horizontal" action="mantenimientos/cod_mant/cod_man_alumno.php" method="post">
	<input name="id_alumno" type="hidden" id="id_alumno" value="<?php echo $alumno->id_alumno; ?>" />

	<?php build($form); submit('Guardar cambios'); ?>
</form>

<script type="application/javascript">
$(function() {
	$('#grado').change(function() {
		$.ajax({
			type: "POST",
			url: "../../actseccion.php",
			data: "grado=" + this.value,
			success: function(msg) {
				$('#seccion').html(msg);
			}
		});
	});
});
 </script>

<?php pie(); ?>