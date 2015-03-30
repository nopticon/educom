<?php

require_once('../conexion.php');

$carne = request_var('carne1', '');

// $anio = date('Y');

$sql = 'SELECT *
	FROM reinscripcion r, grado g, alumno a
	WHERE r.carne = ?
		AND g.id_grado = r.id_grado
		AND r.id_alumno = a.id_alumno';
if (!$alumno = $db->sql_fieldrow(sql_filter($sql, $carne))) {
	location('.');
}

$sql = 'SELECT *
	FROM alumno a, faltas f
	WHERE a.id_alumno = f.id_alumno
		AND a.carne = ?
	ORDER BY f.fecha_falta DESC';
$list = $db->sql_rowset(sql_filter($sql, $carne));

encabezado('Historial de Faltas ' . date('Y'));

?>

<table class="table table-bordered">
	<tbody>
		<tr>
			<td>Carn&eacute;</td>
			<td><?php echo $alumno->carne; ?></td>
		</tr>
		<tr>
			<td>Nombre</td>
			<td><?php echo $alumno->nombre_alumno; ?></td>
		</tr>
		<tr>
			<td>Apellido</td>
			<td><?php echo $alumno->apellido; ?></td>
		</tr>
		<tr>
	  		<td>Grado</td>
			<td><?php echo $alumno->nombre; ?></td>
		</tr>
	</tbody>
</table>

<table class="table table-striped">
	<thead>
		<td width="20%">Fecha de Ingreso</td>
		<td width="10%">Tipo</td>
		<td>Descripci&oacute;n</td>
	</thead>

	<?php foreach ($list as $row) { ?>
	<tr>
		<td><?php echo $row->fecha_falta; ?></td>
		<td><?php echo $row->tipo_falta; ?></td>
		<td><?php echo $row->falta; ?></td>
	</tr>
	<?php } ?>
</table>

<?php pie(); ?>