<?php

require_once('../../conexion.php');

encabezado('Modificaci&oacute;n de Cursos');

$grado = request_var('grado', 0);

$sql = 'SELECT *
	FROM cursos c, grado g
	WHERE g.id_grado = c.id_grado
		AND g.id_grado = ?';
$cursos_grado = $db->sql_rowset(sql_filter($sql, $grado));

?>

<table class="table table-striped">
	<thead>
		<tr>
			<td>Nombre de curso</td>
			<td>Capacidad</td>
			<td>Nombre de grado</td>
		</tr>
	</thead>

	<?php foreach ($cursos_grado as $row) { ?>
		<tr>
			<td><a href="modificar_curso.php?id_curso=<?php echo $row->id_curso; ?>&amp;grado=<?php echo $grado; ?>"><?php echo $row->nombre_curso; ?></a></td>
			<td class="a_center"><?php echo $row->capacidad; ?></td>
			<td><?php echo $row->nombre; ?></td>
		</tr>
	<?php } ?>
</table>

<?php pie();