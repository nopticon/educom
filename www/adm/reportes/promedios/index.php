<?php

$toproot = '../../';
require_once('../../conexion.php');

encabezado('Promedio de Alumnos', '../');

$sql = "SELECT *
	FROM grado
	WHERE status = 'Alta'";
$grado = $db->sql_rowset($sql);

$sql = 'SELECT *
	FROM secciones
	WHERE id_grado = 1';
$secciones = $db->sql_rowset($sql);

$sql = 'SELECT *
	FROM examenes';
$examenes = $db->sql_rowset($sql);

$form = array(
	array(
		'grado' => array(
			'type' => 'select',
			'show' => 'Grado',
			'value' => array()
		),
		'seccion' => array(
			'type' => 'select',
			'show' => 'Secci&oacute;n',
			'value' => array()
		),
		'examen' => array(
			'type' => 'select',
			'show' => 'Unidad',
			'value' => array()
		),
		'anio' => array(
			'type' => 'select',
			'show' => 'Grado',
			'value' => '*'
		)
	)
);

foreach ($grado as $row) {
	$form[0]['grado']['value'][$row->id_grado] = $row->nombre;
}

foreach ($secciones as $row) {
	$form[0]['seccion']['value'][$row->id_seccion] = $row->nombre_seccion;
}

foreach ($examenes as $row) {
	$form[0]['examen']['value'][$row->id_examen] = $row->examen;
}

?>

<form class="form-horizontal" action="listado_alumno1.php" method="post">
	<?php build($form); submit(); ?>
</form>

<script type="text/javascript">
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