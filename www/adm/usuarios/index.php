<?php

require_once('../conexion.php');

encabezado('Ingreso de usuarios');

$form = array(
	'' => array(
		'nombre' => array(
			'type' => 'text',
			'value' => 'Nombre y apellido'
		),
		'usuario' => array(
			'type' => 'text',
			'value' => 'Usuario'
		),
		'password' => array(
			'type' => 'text',
			'value' => 'Contrase&ntilde;a'
		),
	),
);

?>

<form class="form-horizontal" action="cod_usuarios.php" method="post">
	<?php build($form); submit('Crear usuario'); ?>
</form>

<?php pie(); ?>