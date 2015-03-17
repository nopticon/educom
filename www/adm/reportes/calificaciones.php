<?php

require_once('../conexion.php');

encabezado('Tarjeta de Calificaciones');

$sql = 'SELECT *
	FROM grado g, secciones s
	WHERE g.id_grado = s.id_grado
		AND status = ?';
$rowset = $db->sql_rowset(sql_filter($sql, 'Alta'));

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

foreach ($rowset as $row) {
	$form[0]['seccion']['value'][$row->id_seccion] = $row->nombre . ' - ' . $row->nombre_seccion;
}

?>

<div class="small-box">
	<form class="form-horizontal" action="calificaciones2.php" method="post"><?php build($form); submit(); ?></form>
</div>

<?php pie(); ?>