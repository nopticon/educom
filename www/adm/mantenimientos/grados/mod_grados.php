<?php

require_once('../../conexion.php');

encabezado('Modulo de Modificacion de Grados');

$id_grado = $_REQUEST['id_grado'];

$sql = 'SELECT *
	FROM grado
	WHERE id_grado = ?';
$grado = $db->sql_fieldrow(sql_filter($sql, $id_grado));

$form = array(
	'' => array(
		'grado' => array(
			'type' => 'text',
			'value' => 'Nombre del grado',
			'default' => $grado->nombre
		),
		'status' => array(
			'type' => 'select',
			'show' => 'Status',
			'value' => array(
				'Alta' => 'Alta',
				'Baja' => 'Baja'
			),
			'default' => $grado->status
		),
	),
);

?>
<form class="form-horizontal" action="../cod_mant/cod_man_grado.php" method="post">
	<input name="id_grado" type="hidden" id="id_grado" value="<?php echo $grado->id_grado; ?>" />

	<?php build($form); submit(); ?>
</form>

<?php pie(); ?>