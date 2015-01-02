<?php

require_once('../conexion.php');

$sql = "SELECT id_grado, nombre
	FROM grado
	WHERE  status = 'Alta'";
$grado = $db->sql_rowset($sql);

$sql = 'SELECT id_seccion, nombre_seccion
	FROM secciones
	WHERE id_grado = 1';
$seccion = $db->sql_rowset($sql);

encabezado('Asignaci&oacute;n de Areas Ocupacionales');

$form = array(
	'' => array(
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

foreach ($grado as $row) {
	$form['']['grado']['value'][$row->id_grado] = $row->nombre;
}

foreach ($seccion as $row) {
	$form['']['seccion']['value'][$row->id_seccion] = $row->nombre_seccion;
}

?>

<form action="../ocupacional/ocupacional.php" id="form1" name="form1" method="post">
	<?php build($form); submit(); ?>
</form>

<script type="text/javascript">
//<![CDATA[
$(function() {
	$('#grado').change(function() {
		$.ajax({
			type: "POST",
			url: "../actseccion.php",
			data: "grado=" + this.value,
			success: function(msg) {
				$('#seccion').html(msg);
			}
		});
	});
});
//]]>
</script>

<?php pie(); ?>