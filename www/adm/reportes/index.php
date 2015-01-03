<?php

require_once('../conexion.php');

encabezado('Reportes del Sistema');

?>

<div class="ls">
	<ul class="options">
		<li><a href="./alumnos/listado_alumno.php">Listado de Alumnos</a></li>
		<li><a href="./asistencia/listado_alumno.php">Control Asistencia de Alumnos</a></li>
		<li><a href="./promedios/">Promedios de Alumnos</a></li>
		<li><a href="./calificaciones.php">Tarjeta de Calificaciones</a></li>
		<li><a href="./catedraticos/listado_catedratico.php">Catedr&aacute;ticos con Cursos</a></li>
		<li><a href="./certificaciones.php">Certificaciones Anuales</a></li>
		<li><a href="./fgenerales.php">Cuadros Generales de Calificaciones</a></li>
		<li><a href="./carta_editar.php" target="_blank">Carta para Edici&oacute;n de Calificaci&oacute;n</a></li>
	</ul>
</div>

<?php pie(); ?>