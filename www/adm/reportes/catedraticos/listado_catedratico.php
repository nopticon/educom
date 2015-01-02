<?php

require_once('../../conexion.php');

encabezado('Listado de catedr&aacute;ticos con cursos');

$sql = "SELECT *
	FROM grado
	WHERE status = 'Alta'";
$grado = $db->sql_rowset($sql);

$form = array(
	array(
		'grado' => array(
			'type' => 'select',
			'show' => 'Grado',
			'value' => array()
		)
	)
);

foreach ($grado as $row) {
	$form[0]['grado']['value'][$row->id_grado] = $row->nombre;
}

?>

<form class="form-horizontal" action="listado_catedratico1.php" method="post">
	<?php build($form); submit(); ?>
</form>

<script language="JavaScript" type="text/javascript">
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