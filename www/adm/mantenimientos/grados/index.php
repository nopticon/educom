<?php

require_once('../../conexion.php');

$sql = 'SELECT *
	FROM grado';
$list = $db->sql_rowset($sql);

encabezado('Mantenimiento de Grado');

?>

<table width="100%">
	<?php foreach ($list as $row) { ?>
	<tr>
		<td><a href="mod_grados.php?id_grado=<?php echo $row->id_grado; ?>"><?php echo $row->nombre; ?></a></td>
	</tr>
	<?php } ?>
</table>

<?php pie(); ?>