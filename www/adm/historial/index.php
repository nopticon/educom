<?php

require_once('../conexion.php');

encabezado('Modulo de Historial del Alumno');

$form = array(
	array(
		'carne' => array(
			'type' => 'text',
			'value' => 'Carn&eacute;'
		)
	)
);

?>

<div class="small-box">
	<form class="form-horizontal" action="historial.php" method="post"><?php build($form); submit(); ?></form>
</div>

<?php pie(); ?>