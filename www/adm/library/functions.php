<?php

function redirect($to) {
	header('Location: ' . $to);
	exit;
}

function build($fields) {
	foreach ($fields as $field_block => $ary) {
		if (!is_numeric($field_block)) {
			echo '<h6>' . $field_block . '</h6>';
		}

		foreach ($ary as $field_name => $field_data) {
			if (!isset($field_data['default'])) $field_data['default'] = '';

			switch ($field_data['type']) {
				case 'radio':
					echo '<div class="form-group">
						<label for="input' . $field_name . '" class="col-lg-2 control-label">' . $field_name . '</label>
						<div class="col-lg-10 radio">
						';

					$i = 0;
					foreach ($field_data['value'] as $row_name => $row_value) {
						if ($i) echo '&nbsp;&nbsp;&nbsp;';

						$default = ($field_data['default'] == $row_name) ? ' checked="checked"' : '';

						echo '<label class="radio">
							<input' . $default . ' type="radio" name="' . $field_name . '" value="' . $row_name . '" data-toggle="radio"> ' . $row_value . '
						</label>';

						$i++;
					}

					echo '
					</div>
					</div>';
					break;

				case 'select':
					echo '<div class="form-group">
						<label for="input' . $field_name . '" class="col-lg-2 control-label">' . $field_data['show'] . '</label>
						<div class="col-lg-10">
						<select name="' . $field_name . '" id="input' . $field_name . '">';

					$select_year = false;
					if ($field_data['value'] == '*') {
						$field_data['value'] = range(date('Y'), 2010);
						$select_year = true;
					}

					foreach ($field_data['value'] as $row_name => $row_value) {
						$default = ($field_data['default'] == $row_name) ? ' selected="selected"' : '';

						if ($select_year) $row_name = $row_value;

						echo '<option' . $default . ' value="' . $row_name . '">' . $row_value . '</option>';
					}

					echo '</select>
					</div>
					</div>';
					break;

				case 'textarea':
					echo '<div class="form-group">
						<label for="input' . $field_name . '" class="col-lg-2 control-label">' . $field_data['value'] . '</label>
						<div class="col-lg-10">
							<textarea class="form-control" name="' . $field_name . '" id="input' . $field_name . '" autocomplete="off">' . $field_data['default'] . '</textarea>
						</div>
					</div>';
					break;

				default:
					echo '<div class="form-group">
						<label for="input' . $field_name . '" class="col-lg-2 control-label">' . $field_data['value'] . '</label>
						<div class="col-lg-10">
							<input type="text" class="form-control" name="' . $field_name . '" id="input' . $field_name . '" placeholder="' . $field_data['value'] . '" value="' . $field_data['default'] . '" />
						</div>
					</div>';
					break;
			}
		}
	}
}

function submit($value = 'Continuar') {
	echo '<div align="center"><input type="submit" class="btn btn-danger" name="submit" value="' . $value . '" /></div>';
}

function pie() {
	echo '</div></div></body></html>';
}

function encabezado($page_title = '', $ruta = '', $full = true) {
	$nombre = isset($_SESSION['nombre']) ? $_SESSION['nombre'] : '';

	$real_page_title = 'ECP' . (($page_title) ? ': ' . $page_title : '');
	$real_page_title = '.';

?><!DOCTYPE HTML>
<html>
<head>
<meta charset="iso-8859-1" />
<title><?php echo $real_page_title; ?></title>
<!-- <link href='http://fonts.googleapis.com/css?family=Titillium+Web' rel='stylesheet' type='text/css'> -->
<link rel="stylesheet" type="text/css" href="/public/flat-ui/bootstrap/css/bootstrap.css" />
<link rel="stylesheet" type="text/css" href="/public/flat-ui/css/flat-ui.css" />
<link rel="stylesheet" type="text/css" href="/public/css/style.css" />
<link rel="stylesheet" type="text/css" href="/public/kendo/css/kendo.flat.min.css" />
<link rel="stylesheet" type="text/css" href="/public/kendo/css/kendo.common.min.css" />

<script src="/public/js/jquery.js" type="text/javascript"></script>
<script src="/public/flat-ui/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/public/flat-ui/js/jquery.ui.touch-punch.min.js"></script>
<script src="/public/flat-ui/js/bootstrap-select.js"></script>
<script src="/public/flat-ui/js/bootstrap-switch.js"></script>
<script src="/public/flat-ui/js/flatui-checkbox.js"></script>
<script src="/public/flat-ui/js/flatui-radio.js"></script>
<script src="/public/flat-ui/js/jquery.tagsinput.js"></script>
<script src="/public/flat-ui/js/jquery.placeholder.js"></script>
<script src="/public/flat-ui/bootstrap/js/google-code-prettify/prettify.js"></script>
<script src="/public/flat-ui/js/application.js"></script>
<script src="/public/kendo/js/kendo.web.min.js"></script>
<script src="/public/js/ff.js" type="text/javascript"></script>
</head>

<body>
	<div class="container">
		<nav class="navbar navbar-inverse" role="navigation">
			<div class="navbar-header">
				<button type="button" class="navbar-toggle" data-toggle="collapse" data-target=".navbar-ex1-collapse">
					<span class="sr-only">Toggle navigation</span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
					<span class="icon-bar"></span>
				</button>

				<a class="navbar-brand" href="/">Administraci&oacute;n Escolar</a>
			</div>

			<div class="collapse navbar-collapse navbar-ex1-collapse">
				<ul class="nav navbar-nav">
					<?php

					if (!empty($nombre) && $full) {
						echo '<li><a href="#">' . $nombre . '</a></li>';
					}

					?>
					<li class="dropdown active">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">Acciones <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li><a href="/alumnos/index.php" title="Inscripci&oacute;n de Nuevo Alumno">Inscripci&oacute;n</a></li>
							<li><a href="/reinscripcion/index.php" title="Re-Inscripci&oacute;n de Alumno Existente">Re-inscripci&oacute;n</a></li>
							<li><a href="/notas/index.php" title="Visualizaci&oacute;n de Notas">Notas</a></li>
							<li><a href="/historial/index.php" title="Record Acad&eacute;mico del Alumno">Historial de alumno</a></li>
							<li><a href="/reportes/index.php" title="Visualizaci&oacute;n de Reportes Varios">Reportes</a></li>
							<li><a href="/faltas/index.php" title="Visualizaci&oacute;n de Faltas / Ingreso de Faltas Acad&eacute;micas">Faltas acad&eacute;micas</a></li>
							<li><a href="/codigo_alumno/index.php" title="Ingreso de Codigo / Matricula del Alumno">Codigos de alumnos</a></li>
							<li><a href="/ocupacional/index.php" title="Ingreso de Areas Ocupacionales para Alumnos">Cursos ocupacionales</a></li>
						</ul>
					</li>
					<li class="dropdown">
						<a href="#" class="dropdown-toggle" data-toggle="dropdown">Admin <b class="caret"></b></a>
						<ul class="dropdown-menu">
							<li><a href="/mantenimientos/alumnos/">Modificaci&oacute;n de alumnos</a></li>
							<li><a href="/aux_search/index.php">B&uacute;squeda de alumnos</a></li>
							<li><a href="/ingreso_index.php" title="Ingreso de datos">Ingreso de datos</a></li>
							<li><a href="/editar/index.php" title="Edicion de notas">Edicion de notas</a></li>
							<li><a href="/mantenimientos/index.php" title="Mantenimientos">Mantenimientos</a></li>
						</ul>
					</li>
				</ul>

				<?php

				if (!empty($nombre) && $full) {
					echo '<ul class="nav navbar-nav navbar-right"><li><a href="exit.php">Cerrar sesi&oacute;n</a></li></ul>';
				}

				?>
			</div><!-- /.navbar-collapse -->
		</nav>

		<div id="content">
			<h5><?php echo $page_title; ?></h5>
<?php

}

function encabezado_simple($page_title = '', $ruta = '', $full = true) {
	$nombre = $_SESSION['nombre'];

	$real_page_title = 'ECP' . (($page_title) ? ': ' . $page_title : '');
	$real_page_title = '.';

?><!DOCTYPE HTML>
<html>
<head>
<meta charset="iso-8859-1" />
<title><?php echo $real_page_title; ?></title>
<!-- <link href='http://fonts.googleapis.com/css?family=Titillium+Web' rel='stylesheet' type='text/css'> -->
<link rel="stylesheet" type="text/css" href="/public/flat-ui/bootstrap/css/bootstrap.css" />
<link rel="stylesheet" type="text/css" href="/public/flat-ui/css/flat-ui.css" />
<link rel="stylesheet" type="text/css" href="/public/css/style.css" />
<link rel="stylesheet" type="text/css" href="/public/kendo/css/kendo.flat.min.css" />
<link rel="stylesheet" type="text/css" href="/public/kendo/css/kendo.common.min.css" />

<script src="/public/js/jquery.js" type="text/javascript"></script>
<script src="/public/flat-ui/js/bootstrap.min.js" type="text/javascript"></script>
<script src="/public/flat-ui/js/jquery.ui.touch-punch.min.js"></script>
<script src="/public/flat-ui/js/bootstrap-select.js"></script>
<script src="/public/flat-ui/js/bootstrap-switch.js"></script>
<script src="/public/flat-ui/js/flatui-checkbox.js"></script>
<script src="/public/flat-ui/js/flatui-radio.js"></script>
<script src="/public/flat-ui/js/jquery.tagsinput.js"></script>
<script src="/public/flat-ui/js/jquery.placeholder.js"></script>
<script src="/public/flat-ui/bootstrap/js/google-code-prettify/prettify.js"></script>
<script src="/public/flat-ui/js/application.js"></script>
<script src="/public/kendo/js/kendo.web.min.js"></script>
<script src="/public/js/ff.js" type="text/javascript"></script>
</head>

<body>
	<div class="container">
		<div id="content">
<?php

}

?>