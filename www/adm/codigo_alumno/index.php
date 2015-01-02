<?php

require_once('../conexion.php');

$sql = 'SELECT *
	FROM grado
	WHERE status = ?';
$grado = $db->sql_rowset($db->__prepare($sql, 'Alta'));

$sql = 'SELECT *
	FROM secciones
	WHERE id_grado = 1';
$seccion = $db->sql_rowset($sql);

$form = array(
	array(
		'grado' => array(
			'type' => 'select',
			'show' => 'Grado',
			'value' => array()
		),
		'seccion' => array(
			'type' => 'select',
			'show' => 'Curso',
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
	$form[0]['grado']['value'][$row->id_grado] = $row->nombre;
}

foreach ($seccion as $row) {
	$form[0]['seccion']['value'][$row->id_seccion] = $row->nombre_seccion;
}

encabezado('Ingresar C&oacute;digos de Alumnos');

?>

<form class="form-horizontal" method="post" action="codigos.php">
	<?php build($form); submit(); ?>
</form>

<script type="text/javascript">
//<![CDATA[
$(function() {
	$('#grado').change(function() {
		$.ajax({
			type: "POST",
			url: "../actseccion.php",
			data: "grado=" + $(this).val(),
			success: function(msg) {
				$('#seccion').html(msg);
			}
		});
	});
});
//]]>
</script>

<?php pie(); ?>