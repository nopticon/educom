<?php

require_once('../../conexion.php');

encabezado('Listado de alumnos');

$sql = "SELECT *
	FROM grado
	WHERE status = 'Alta'";
$grados = $db->sql_rowset($sql);

$sql = 'SELECT *
	FROM secciones
	WHERE id_grado = 1';
$secciones = $db->sql_rowset($sql);

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
		'anio' => array(
			'type' => 'select',
			'show' => 'A&ntilde;o',
			'value' => '*'
		)
	)
);

foreach ($grados as $row) {
	$form[0]['grado']['value'][$row->id_grado] = $row->nombre;
}

foreach ($secciones as $row) {
	$form[0]['seccion']['value'][$row->id_seccion] = $row->nombre_seccion;
}

?>

<form class="form-horizontal" action="listado_alumno1.php" method="post" target="_blank">
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