<?php

require_once('../conexion.php');

encabezado('Cuadros Generales de Calificaciones');

$sql = "SELECT *
	FROM grado g, secciones s
	WHERE g.id_grado = s.id_grado
		AND status = 'Alta'";
$grado_seccion = $db->sql_rowset($sql);

$sql = 'SELECT *
	FROM examenes';
$examenes = $db->sql_rowset($sql);

$form = array(
	array(
		'seccion' => array(
			'type' => 'select',
			'show' => 'Grado',
			'value' => array()
		),
		'examen' => array(
			'type' => 'select',
			'show' => 'Unidad',
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

foreach ($examenes as $row) {
	$form[0]['examen']['value'][$row->id_examen] = $row->examen;
}

?>

<form class="form-horizontal" action="fgenerales2.php" method="post" target="_blank">
	<?php build($form); submit(); ?>
</form>

<?php pie(); ?>