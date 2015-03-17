<?php

require_once('../conexion.php');

$alumno = (isset($_REQUEST) && isset($_REQUEST['alumno'])) ? $_REQUEST['alumno'] : 0;

$sql = 'SELECT id_alumno, nombre_alumno, apellido
	FROM alumno
	WHERE id_alumno = ?';
$alumno = $db->sql_fieldrow(sql_filter($sql, $alumno));

$sql = "SELECT *
	FROM grado g, secciones s
	WHERE g.id_grado = s.id_grado
		AND status = 'Alta'";
$grado_seccion = $db->sql_rowset($sql);

encabezado('Certificaciones Anuales');

$form = array(
	array(
		'seccion' => array(
			'type' => 'select',
			'show' => 'Grado',
			'value' => array()
		),
		'anio' => array(
			'type' => 'select',
			'show' => 'A&ntilde;o',
			'value' => '*'
		)
	)
);

foreach ($grado_seccion as $row) {
	$form[0]['seccion']['value'][$row->id_seccion] = $row->nombre . ' - ' . $row->nombre_seccion;
}

?>

<form action="certificaciones2.php" method="post" class="form-horizontal" target="_blank">
	<?php if ($alumno) { ?>
	<input type="hidden" name="alumno" value="<?php echo $alumno->id_alumno; ?>" />

	<h6><?php echo $alumno->nombre_alumno . ' ' . $alumno->apellido; ?></h6>
	<?php } ?>

	<?php build($form); submit(); ?>
</form>

<?php pie(); ?>