<li class="dropdown active">
	<a href="#" class="dropdown-toggle" data-toggle="dropdown">Administraci&oacute;n</a>
	<ul class="dropdown-menu">
		<li><a href="<?php echo a('alumnos/index.php'); ?>" title="Inscripci&oacute;n de Nuevo Alumno">Inscripci&oacute;n</a></li>
		<li><a href="<?php echo a('reinscripcion/index.php'); ?>" title="Re-Inscripci&oacute;n de Alumno Existente">Re-inscripci&oacute;n</a></li>
		<li><a href="<?php echo a('notas/index.php'); ?>" title="Visualizaci&oacute;n de Notas">Notas</a></li>
		<li><a href="<?php echo a('historial/index.php'); ?>" title="Record Acad&eacute;mico del Alumno">Historial de alumno</a></li>
		<li><a href="<?php echo a('reportes/index.php'); ?>" title="Visualizaci&oacute;n de Reportes Varios">Reportes</a></li>
		<li><a href="<?php echo a('faltas/index.php'); ?>" title="Visualizaci&oacute;n de Faltas / Ingreso de Faltas Acad&eacute;micas">Faltas acad&eacute;micas</a></li>
		<li><a href="<?php echo a('codigo_alumno/index.php'); ?>" title="Ingreso de Codigo / Matricula del Alumno">Codigos de alumnos</a></li>
		<li><a href="<?php echo a('ocupacional/index.php'); ?>" title="Ingreso de Areas Ocupacionales para Alumnos">Cursos ocupacionales</a></li>

		<?php if ($user->is('founder')) { ?>
		<li><a href="<?php echo a('mantenimientos/alumnos/'); ?>">Modificaci&oacute;n de alumnos</a></li>
		<li><a href="<?php echo a('aux_search/index.php'); ?>">B&uacute;squeda de alumnos</a></li>
		<li><a href="<?php echo a('editar/index.php'); ?>" title="Edicion de notas">Edicion de notas</a></li>
		<li><a href="<?php echo a('mantenimientos/index.php'); ?>" title="Mantenimientos">Mantenimientos</a></li>
		<?php } ?>
	</ul>
</li>