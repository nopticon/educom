<?php

require_once('../conexion.php');

$id_alumno = request_var('id_alumno', 0);

$sql = 'SELECT *
	FROM alumno
	WHERE id_alumno = ?';
if (!$alumno = $db->sql_fieldrow(sql_filter($sql, $id_alumno))) {
	header('Location: index.php');
	exit;
}

encabezado('Datos de Alumno', '', false);

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

<table class="table table-striped">
	<?php

	foreach ($list as $list_name => $list_show) { ?>
	<tr>
		<td><?php echo $list_show; ?></td>
		<td><?php echo $alumno->$list_name; ?></td>
	</tr>
	<?php } ?>
</table>

<div class="text-center"><a href="index.php" class="btn btn-danger">Continuar</a></div>

<?php pie(); ?>