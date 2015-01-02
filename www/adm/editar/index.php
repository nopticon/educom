<?php

require_once('../conexion.php');

encabezado('Edici&oacute;n de Notas');

$sql = 'SELECT *
	FROM grado g, secciones s
	WHERE g.id_grado = s.id_grado
		AND status = ?';
$grado = $db->sql_rowset($db->__prepare($sql, 'Alta'));

$sql = 'SELECT *
	FROM cursos
	WHERE id_grado = 1';
$curso = $db->sql_rowset($sql);

$sql = 'SELECT *
	FROM examenes';
$examen = $db->sql_rowset($sql);

$form = array(
	array(
		'grado' => array(
			'type' => 'select',
			'show' => 'Grado',
			'value' => array()
		),
		'curso' => array(
			'type' => 'select',
			'show' => 'Curso',
			'value' => array()
		),
		'examen' => array(
			'type' => 'select',
			'show' => 'Examen',
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
	$form[0]['grado']['value'][$row->id_seccion] = $row->nombre . ' - ' . $row->nombre_seccion;
}

foreach ($curso as $row) {
	$form[0]['curso']['value'][$row->id_curso] = $row->nombre_curso;
}

foreach ($examen as $row) {
	$form[0]['examen']['value'][$row->id_examen] = $row->examen;
}

?>

<h6>Edici&oacute;n de notas</h6>

<form class="form-horizontal" action="notas.php" method="post">
	<?php build($form); submit(); ?>
</form>

<script type="text/javascript">
//<![CDATA[
$(function() {
	$('#grado').change(function() {
		$.ajax({
			type: "POST",
			url: "./index2.php",
			data: "grado=" + this.value,
			success: function(msg) {
				$('#curso').html(msg);
			}
		});
	});
});
//]]>
</script>

<?php pie(); ?>