<?php

require_once('conexion.php');

encabezado('Ingreso de datos');

?>

<ul class="options">
	<?php

	$show = w('areas catedraticos examenes grados secciones cursos usuarios');

	foreach ($show as $row) {
		echo '<li><a href="/' . $row . '/index.php">' . ucfirst($row) . '</a></li>';
	}

	?>
</ul>

<?php pie(); ?>