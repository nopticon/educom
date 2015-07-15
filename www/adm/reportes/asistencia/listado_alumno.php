<?php

require_once('../../conexion.php');

encabezado('Asistencia de Alumnos');

$sql = "SELECT *
	FROM grado
	WHERE status = 'Alta'";
$grados = $db->sql_rowset($sql);

$sql = 'SELECT *
	FROM secciones
	WHERE id_grado = 1';
$secciones = $db->sql_rowset($sql);

$form = [[
	'grado' => [
		'type' => 'select',
		'show' => 'Grado',
		'value' => []
	],
	'seccion' => [
		'type' => 'select',
		'show' => 'Secci&oacute;n',
		'value' => []
	],
	'dateselect' => [
		'type' => 'calendar',
		'value' => 'Fecha'
	]
]];

foreach ($grados as $row) {
	$form[0]['grado']['value'][$row->id_grado] = $row->nombre;
}

foreach ($secciones as $row) {
	$form[0]['seccion']['value'][$row->id_seccion] = $row->nombre_seccion;
}

?>

<form class="form-horizontal" action="listado_alumno1.php" method="post" target="_blank">
	<?php echo build_form($form); submit(); ?>
</form>

<?php pie(); ?>