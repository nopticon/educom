<?php

require_once('../conexion.php');

$sql = 'SELECT *
	FROM grado
	ORDER BY id_grado DESC';
$list = $db->sql_rowset($sql);

encabezado('Grados');

?>

<table width="100%">
	<?php

	foreach ($list as $row) {
	?>
	<tr>
		<td width="438"><?php echo $row->nombre; ?></td>
		<td width="128" align="center"><?php echo $row->seccion; ?></td>
		<td width="111" align="center"><?php echo $row->status; ?></td>
	</tr>
	<?php
	}

	?>
</table>

<?php pie(); ?>