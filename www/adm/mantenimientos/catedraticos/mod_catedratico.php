<?php

require_once('../../conexion.php');

$id_catedratico = $_REQUEST['id_catedratico'];

$sql = 'SELECT *
	FROM catedratico
	WHERE id_catedratico = ?';
if (!$catedratico = $db->sql_fieldrow($db->__prepare($sql, $id_catedratico))) {
	redirect('index.php');
}

encabezado('Modificaci&oacute;n de Catedr&aacute;tico');

$form = array(
	'' => array(
		'nombre' => array(
			'type' => 'text',
			'value' => 'Nombre',
			'default' => $catedratico->nombre_catedratico
		),
		'apellido' => array(
			'type' => 'text',
			'value' => 'Apellido',
			'default' => $catedratico->apellido
		),
		'profesion' => array(
			'type' => 'text',
			'value' => 'Profesi&oacute;n',
			'default' => $catedratico->profesion
		),
		'email' => array(
			'type' => 'text',
			'value' => 'Correo electr&oacute;nico',
			'default' => $catedratico->email
		),
		'telefonos' => array(
			'type' => 'text',
			'value' => 'Tel&eacute;fono',
			'default' => $catedratico->telefono
		),
		'direccion' => array(
			'type' => 'text',
			'value' => 'Direcci&oacute;n',
			'default' => $catedratico->direccion
		),
		'observacion' => array(
			'type' => 'text',
			'value' => 'Observaci&oacute;n',
			'default' => $catedratico->observacion
		),

	),
);

?>

<form class="form-horizontal" action="../cod_mant/cod_man_catedratico.php" method="post">
	<input name="id_catedratico" type="hidden" value="<?php echo $catedratico->id_catedratico; ?>" />

	<?php build($form); submit(); ?>
</form>

<?php pie(); ?>