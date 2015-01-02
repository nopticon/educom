<?php

require_once('../../conexion.php');

encabezado('Modificaci&oacute;n de Catedr&aacute;ticos');

$sql = 'SELECT *
	FROM catedratico';
$catedratico = $db->sql_rowset($sql);

?>

<table width="100%">
	<thead>
		<td>Apellido</td>
		<td>Nombre</td>
	</thead>

	<?php foreach ($catedratico as $row) { ?>
		<tr>
			<td><a href="mod_catedratico.php?id_catedratico=<?php echo $row->id_catedratico; ?>"><?php echo $row->apellido; ?></a></td>
			<td><a href="mod_catedratico.php?id_catedratico=<?php echo $row->id_catedratico; ?>"><?php echo $row->nombre_catedratico; ?></a></td>
		</tr>
	<?php } ?>
</table>

<?php pie(); ?>