<?php

require_once('../conexion.php');

$sql = 'SELECT DISTINCT *	
	FROM alumno a, faltas f
	WHERE a.id_alumno = f.id_alumno
	GROUP BY a.carne
	ORDER BY a.apellido, a.nombre_alumno DESC
	LIMIT 300';
$list = $db->sql_rowset($sql);

encabezado('Historial de Faltas Acad&eacute;micas');

?>

<table width="100%" class="tr_x0">
	<thead>
		<td>Carn&eacute;</td>
		<td>Apellido</td>
		<td>Nombre</td>
	</thead>
	
	<?php foreach ($list as $row) { ?>
		<tr>
			<td width="20%" class="a_center"><a href="ver_faltas.php?id_falta=<?php echo $row->id_falta; ?>"><?php echo $row->carne; ?></a></td>
			<td><a href="ver_faltas.php?id_falta=<?php echo $row->id_falta; ?>"><?php echo $row->apellido; ?></a></td>
			<td><a href="ver_faltas.php?id_falta=<?php echo $row->id_falta; ?>"><?php echo $row->nombre_alumno; ?></a></td>
		</tr>
	<?php } ?>
</table>

<?php pie(); ?>