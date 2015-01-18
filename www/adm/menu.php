<?php

$menu_list = array(
	array('href' => 'alumnos/', 'title' => 'Inscripci&oacute;n', 'auth' => 'founder'),
	array('href' => 'reinscripcion/', 'title' => 'Re-Inscripci&oacute;n', 'auth' => 'founder'),
	array('href' => 'notas/', 'title' => 'Notas', 'auth' => 'founder'),
	array('href' => 'historial/', 'title' => 'Historial de alumno', 'auth' => 'teacher'),
	array('href' => 'reportes/', 'title' => 'Reportes', 'auth' => 'teacher'),
	array('href' => 'faltas/', 'title' => 'Faltas Acad&eacute;micas', 'auth' => 'teacher'),
	array('href' => 'codigo_alumno/', 'title' => 'C&oacute;digos de alumnos', 'auth' => 'founder'),
	// array('href' => 'ocupacional/', 'title' => 'Cursos ocupacionales', 'auth' => 'founder'),
	array('href' => 'mantenimientos/alumnos/', 'title' => 'Modificaci&oacute;n de alumnos', 'auth' => 'founder'),
	array('href' => 'aux_search/', 'title' => 'B&uacute;squeda de alumnos', 'auth' => 'founder'),
	array('href' => 'editar/', 'title' => 'Edici&oacute;n de notas', 'auth' => 'founder'),
	array('href' => 'mantenimientos/', 'title' => 'Mantenimientos', 'auth' => 'founder')
);

?>

<li class="dropdown active">
	<a href="#" class="dropdown-toggle" data-toggle="dropdown">Administraci&oacute;n</a>
	<ul class="dropdown-menu">
		<?php

		foreach ($menu_list as $row) {
			if (!$user->is($row['auth'])) continue;

			echo '<li><a href="' . a($row['href']) . '" title="' . $row['title'] . '">' . $row['title'] . '</a></li>';
		}

		?>
	</ul>
</li>