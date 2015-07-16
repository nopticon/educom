<?php

require_once('../conexion.php');

$carne = request_var('carne1', '');

$sql = 'SELECT a.id_alumno, a.carne, a.nombre_alumno, a.apellido, g.nombre AS nombre_grado, s.nombre_seccion
	FROM alumno a
	INNER JOIN reinscripcion r ON r.id_alumno = a.id_alumno
	INNER JOIN grado g ON g.id_grado = r.id_grado
	INNER JOIN secciones s ON s.id_seccion = r.id_seccion
	WHERE r.carne = ?';
if (!$alumno = $db->sql_fieldrow(sql_filter($sql, $carne))) {
	location('.');
}

$sql = 'SELECT *
	FROM faltas f
	INNER JOIN cursos c ON c.id_curso = f.course_id
	INNER JOIN catedratico a ON a.id_member = f.teacher_id
	WHERE f.id_alumno = ?
	ORDER BY f.id_falta DESC';
$list = $db->sql_rowset(sql_filter($sql, $alumno->id_alumno));

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
		<?php if ($alumno->apellido) { ?>
		<tr>
			<td>Apellido</td>
			<td><?php echo $alumno->apellido; ?></td>
		</tr>
		<?php } ?>
		<tr>
	  		<td>Grado</td>
			<td><?php echo $alumno->nombre_grado . ' ' . $alumno->nombre_seccion; ?></td>
		</tr>
	</tbody>
</table>

<table class="table table-striped">
	<thead>
		<td width="20%">Fecha de Ingreso</td>
		<td width="10%">Tipo</td>
		<td>Descripci&oacute;n</td>
		<td>Curso</td>
		<td>Catedr&aacute;tico</td>
	</thead>

	<?php foreach ($list as $row) { ?>
	<tr>
		<td><?php echo $row->fecha_falta; ?></td>
		<td><?php echo $row->tipo_falta; ?></td>
		<td><?php echo $row->falta; ?></td>
		<td><?php echo $row->nombre_curso; ?></td>
		<td><?php echo $row->nombre_catedratico; ?></td>
	</tr>
	<?php } ?>
</table>

<?php pie(); ?>