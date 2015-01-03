<?php

// function redirect($to) {
// 	header('Location: ' . $to);
// 	exit;
// }

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
?>
<span class="clear"></span>

</div>
</div>

</body>
</html>
<?php
}

function encabezado($page_title = '', $ruta = '', $full = true) {
	global $config, $user;

	$is_member = $user->is('member');
	$real_page_title = $config->sitename . (($page_title) ? ': ' . $page_title : '');

?><!DOCTYPE HTML>
<html>
<head>
<meta charset="utf-8" />
<title><?php echo $real_page_title; ?></title>
<link rel="stylesheet" type="text/css" href="<?php echo a('public/flat-ui/bootstrap/css/bootstrap.css'); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo a('public/flat-ui/css/flat-ui.css'); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo a('public/kendo/css/kendo.flat.min.css'); ?>" />
<link rel="stylesheet" type="text/css" href="<?php echo a('public/kendo/css/kendo.common.min.css'); ?>" />
<link rel="stylesheet" type="text/css" href="/assets/default.css?g=1368227590" />

<script src="<?php echo a('public/js/jquery.js'); ?>" type="text/javascript"></script>
<script src="<?php echo a('public/flat-ui/js/bootstrap.min.js'); ?>" type="text/javascript"></script>
<script src="<?php echo a('public/flat-ui/js/jquery.ui.touch-punch.min.js'); ?>"></script>
<script src="<?php echo a('public/flat-ui/js/bootstrap-select.js'); ?>"></script>
<script src="<?php echo a('public/flat-ui/js/bootstrap-switch.js'); ?>"></script>
<script src="<?php echo a('public/flat-ui/js/flatui-checkbox.js'); ?>"></script>
<script src="<?php echo a('public/flat-ui/js/flatui-radio.js'); ?>"></script>
<script src="<?php echo a('public/flat-ui/js/jquery.tagsinput.js'); ?>"></script>
<script src="<?php echo a('public/flat-ui/js/jquery.placeholder.js'); ?>"></script>
<script src="<?php echo a('public/flat-ui/bootstrap/js/google-code-prettify/prettify.js'); ?>"></script>
<script src="<?php echo a('public/flat-ui/js/application.js'); ?>"></script>
<script src="<?php echo a('public/kendo/js/kendo.web.min.js'); ?>"></script>
<script src="<?php echo a('public/js/ff.js'); ?>" type="text/javascript"></script>
</head>

<body>
	<div class="page">
	<div class="header">
		<div class="brand">
			<h1><a href="."><?php echo $config->sitename; ?></a></h1>
		</div>

		<?php if (!$is_member) { ?>
		<form action="/signin/" method="get">
			<div class="a_right ctl"><input type="submit" value="{L_USER_IDENTITY}" /></div>
		</form>
		<?php } else if ($user->is('founder')) { ?>
		<form action="/acp/" method="get">
			<div class="a_right ctl"><input type="submit" value="Administrador" /></div>
		</form>
		<?php } ?>

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
							<?php require_once(__DIR__ . '/../menu.php'); ?>
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
	$real_page_title = 'ECP' . (($page_title) ? ': ' . $page_title : '');

?><!DOCTYPE HTML>
<html>
<head>
<meta charset="iso-8859-1" />
<title><?php echo $real_page_title; ?></title>
<!-- <link href='http://fonts.googleapis.com/css?family=Titillium+Web' rel='stylesheet' type='text/css'> -->
<link rel="stylesheet" type="text/css" href="public/flat-ui/bootstrap/css/bootstrap.css" />
<link rel="stylesheet" type="text/css" href="public/flat-ui/css/flat-ui.css" />
<link rel="stylesheet" type="text/css" href="public/css/style.css" />
<link rel="stylesheet" type="text/css" href="public/kendo/css/kendo.flat.min.css" />
<link rel="stylesheet" type="text/css" href="public/kendo/css/kendo.common.min.css" />

<script src="public/js/jquery.js" type="text/javascript"></script>
<script src="public/flat-ui/js/bootstrap.min.js" type="text/javascript"></script>
<script src="public/flat-ui/js/jquery.ui.touch-punch.min.js"></script>
<script src="public/flat-ui/js/bootstrap-select.js"></script>
<script src="public/flat-ui/js/bootstrap-switch.js"></script>
<script src="public/flat-ui/js/flatui-checkbox.js"></script>
<script src="public/flat-ui/js/flatui-radio.js"></script>
<script src="public/flat-ui/js/jquery.tagsinput.js"></script>
<script src="public/flat-ui/js/jquery.placeholder.js"></script>
<script src="public/flat-ui/bootstrap/js/google-code-prettify/prettify.js"></script>
<script src="public/flat-ui/js/application.js"></script>
<script src="public/kendo/js/kendo.web.min.js"></script>
<script src="public/js/ff.js" type="text/javascript"></script>
</head>

<body>
	<div class="container">
		<div id="content">
<?php

}

?>