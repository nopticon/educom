<?php

if (!isset($toproot)) {
	$toproot = '../';
}

?>

<div id="tmenu">
    <ul id="menu" class="float-holder">
		<li><a href="/alumnos/index.php" title="Inscripci&oacute;n de Nuevo Alumno">Inscripci&oacute;n</a></li>
		<li><a href="/reinscripcion/index.php" title="Re-Inscripci&oacute;n de Alumno Existente">Re-inscripci&oacute;n</a></li>
		<li><a href="/notas/index.php" title="Visualizaci&oacute;n de Notas">Notas</a></li>
		<li><a href="/historial/index.php" title="Record Acad&eacute;mico del Alumno">Historial de alumno</a></li>
		<li><a href="/reportes/index.php" title="Visualizaci&oacute;n de Reportes Varios">Reportes</a></li>
		<li><a href="/faltas/index.php" title="Visualizaci&oacute;n de Faltas / Ingreso de Faltas Acad&eacute;micas">Faltas acad&eacute;micas</a></li>
		<li><a href="/codigo_alumno/index.php" title="Ingreso de Codigo / Matricula del Alumno">Codigos de alumnos</a></li>
		<li><a href="/ocupacional/index.php" title="Ingreso de Areas Ocupacionales para Alumnos">Cursos ocupacionales</a></li>
		<li><a href="/mantenimientos/alumnos/">Modificaci&oacute;n de alumnos</a></li><br />
		<li><a onclick="return buscar('/aux_search/index.php'); " href="#">B&uacute;squeda de alumno</a></li>
		<?php

		if (isset($_SESSION['userlog']) && ($_SESSION['userlog'] == 'Director')) {
			?>
			<li><a href="/ingreso_index.php" title="Ingreso de datos">Ingreso de datos</a></li>
			<li><a href="/editar/index.php" title="Edicion de notas">Edicion de notas</a></li>
			<li><a href="/mantenimientos/index.php" title="Mantenimientos">Mantenimientos</a></li>
			<?php
		}

		?>
	</ul>
</div>