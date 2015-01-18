<?php

function build($fields) {
	foreach ($fields as $field_block => $ary) {
		if (!is_numeric($field_block)) {
			echo '<h6>' . $field_block . '</h6><br />';
		}

		foreach ($ary as $field_name => $field_data) {
			if (!isset($field_data['show'])) $field_data['show'] = $field_name;

			if (!isset($field_data['default'])) $field_data['default'] = '';

			switch ($field_data['type']) {
				case 'radio':
					echo '<div class="form-group">
						<label for="input' . $field_name . '" class="col-lg-2 control-label">' . $field_data['show']	 . '</label>
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
						<select class="form-control select select-primary mbl" name="' . $field_name . '" id="input' . $field_name . '">';

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
?>
<span class="clear"></span>

</div></div>

</body>
</html>
<?php
}

function get_header($page_title = '', $ruta = '', $full = true) {
	global $config, $user;

	$is_member = $user->is('member');
	$real_page_title = $config->sitename . (($page_title) ? ': ' . $page_title : '');

?><!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8" />
<title><?php echo $real_page_title; ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo a('public/flat-ui/css/vendor/bootstrap.min.css'); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo a('public/flat-ui/css/flat-ui.min.css'); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo a('public/kendo/css/kendo.flat.min.css'); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo a('public/kendo/css/kendo.common.min.css'); ?>" />
<link rel="stylesheet" type="text/css" href="/assets/default.css?g=1368227590" />

<script src="//ajax.googleapis.com/ajax/libs/jquery/1.10.2/jquery.min.js"></script>
<script>window.jQuery || document.write('<script src="<?php echo a('public/js/jquery.js'); ?>">\x3C/script>')</script>
<script src="<?php echo a('public/flat-ui/js/flat-ui.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo a('public/kendo/js/kendo.web.min.js'); ?>"></script>
<script src="<?php echo a('public/js/ff.js'); ?>" type="text/javascript"></script>
</head>

<body>
	<div class="page">
<?php
}

function encabezado($page_title = '', $ruta = '', $full = true) {
	global $config, $user;

	$is_member = $user->is('member');

	$menu_list = array(
		array('href' => 'alumnos/', 'title' => 'Inscripci&oacute;n', 'auth' => 'student'),
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

	$enabled_items = [];
	foreach ($menu_list as $row) {
		if (!$user->is($row['auth'])) continue;

		$enabled_items[] = [
			'href' => a($row['href']),
			'title' => $row['title']
		];
	}

	get_header($page_title, $ruta, $full);

?>
	<div class="header">
		<div class="brand">
			<h1><a href="."><?php echo $config->sitename; ?></a></h1>
		</div>

		<?php if ($is_member) { ?>
		<div id="menu">
			<ul>
				<li><a href="/news/" title="Noticias">Noticias</a></li>
				<li><a href="/events/" title="Eventos">Eventos</a></li>
				<li><a href="/board/" title="Foro">Foro</a></li>
				<li><a href="/community/" title="Comunidad">Comunidad</a></li>
				<li>
					<div class="collapse navbar-ex1-collapse" style="display: block;">
						<ul>
							<?php if ($enabled_items) { ?>
							<li class="dropdown active">
								<a href="#" class="dropdown-toggle" data-toggle="dropdown">Opciones</a>
								<ul class="dropdown-menu">

								<?php

								foreach ($enabled_items as $row) {
									echo '<li><a href="' . $row['href'] . '" title="' . $row['title'] . '">' . $row['title'] . '</a></li>';
								}

								?>

								</ul>
							</li>
							<?php } ?>
						</ul>
					</div>
				</li>
			</ul>
		</div>
		<?php } ?>

		<span class="clear"></span>
	</div>

	<div id="content">
		<br />

		<div class="h">
			<h3><?php echo $page_title; ?></h3>
		</div>

		<br />

<?php

}

function encabezado_simple($page_title = '', $ruta = '', $full = true) {
	get_header($page_title, $ruta, $full);

	echo '<div id="content">';
}