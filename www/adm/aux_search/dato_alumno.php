<?php

require_once('../conexion.php');

$id_alumno = $_REQUEST['id_alumno'];

$sql = 'SELECT *
	FROM alumno
	WHERE id_alumno = ?';
if (!$alumno = $db->sql_fieldrow($db->__prepare($sql, $id_alumno))) {
	header('Location: index.php');
	exit;
}

encabezado('Datos Generales del Alumno', '', false);

$list = array(
	'codigo_alumno' => 'C&oacute;digo',
	'carne' => 'Carn&eacute;',
	'nombre_alumno' => 'Nombre',
	'apellido' => 'Apellido',
	'direccion' => 'Direcci&oacute;n',
	'telefono1' => 'Tel&eacute;fono',
	'email' => 'Email',
	'padre' => 'Padre',
	'madre' => 'Madre'
);

?>

<div class="small-box">
	<?php

	foreach ($list as $list_name => $list_show) {
		if (empty($alumno->$list_name)) $alumno->$list_name = '-';

		echo '
		<div class="form-group">
			<label class="col-lg-2 control-label">' . $list_show . '</label>
			<div class="col-rg-10">' . $alumno->$list_name . '</div>
		</div>';
	}

	?>

	<a href="index.php" class="btn btn-danger">Continuar</a>
</div>

<?php pie(); ?>