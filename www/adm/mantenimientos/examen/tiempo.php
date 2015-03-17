<?php

require_once('../../conexion.php');

$id_examen = $_REQUEST['id_examen'];

encabezado('Modificaci&oacute;n de Unidad');

$sql = 'SELECT *
	FROM examenes
	WHERE id_examen = ?';
$examen = $db->sql_fieldrow(sql_filter($sql, $id_examen));

$form = array(
	array(
		'examen' => array(
			'type' => 'text',
			'value' => 'Unidad',
			'default' => $examen->examen
		),
		'observacion' => array(
			'type' => 'text',
			'value' => 'Observaciones',
			'default' => $examen->observacion
		),
		'status' => array(
			'type' => 'select',
			'show' => 'Status',
			'value' => array(
				'Alta' => 'Alta',
				'Baja' => 'Baja'
			),
			'default' => $examen->status
		)
	)
);

?>

<form class="form-horizontal" action="../cod_mant/cod_man_examen.php" method="post">
	<input name="id_examen" type="hidden" value="<?php echo $id_examen; ?>" />

	<?php build($form); submit(); ?>
</form>

<?php pie(); ?>