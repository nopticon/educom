<?php

require_once('../conexion.php');

$sql = 'SELECT *
	FROM alumno a, faltas f
	WHERE a.id_alumno = f.id_alumno
	ORDER BY f.id_falta DESC
	LIMIT 300';
$list = $db->sql_rowset($sql);

encabezado('Historial de Faltas Acad&eacute;micas');

?>

<table width="100%">
	<thead>
		<td>Carn&eacute;</td>
		<td>Apellido</td>
		<td>Nombre</td>
		<td>Ver falta</td>
	</thead>
	<?php

	foreach ($list as $row) {
	?>
		<tr>
			<td width="20%" class="a_center"><?php echo $row->carne; ?></td>
			<td><?php echo $row->apellido; ?></td>
			<td><?php echo $row->nombre_alumno; ?></td>
			<td width="15%" class="a_center"><a href="ver_faltas.php?id_falta=<?php echo $row->id_falta; ?>" target="_blank">Ver Faltas</a></td>
		</tr>
	<?php
	}

	?>
</table>

<?php pie(); ?>