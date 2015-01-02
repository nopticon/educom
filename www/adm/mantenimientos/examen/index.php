<?php

require_once('../../conexion.php');

encabezado('Modificaci&oacute;n de Unidades');

$sql = 'SELECT *
	FROM examenes';
$examenes = $db->sql_rowset($sql);

?>

<table width="100%">
	<thead>
		<td>Nombre de unidad</td>
		<td>Fecha de ingreso</td>
		<td>Status</td>
	</thead>

	<?php foreach ($examenes as $row) { ?>
	<tr>
		<td><a href="tiempo.php?id_examen=<?php echo $row->id_examen; ?>"><?php echo $row->examen; ?></a></td>
		<td class="a_center"><?php echo $row->fecha_ingreso; ?></td>
		<td class="a_center"><?php echo $row->status; ?></td>
	</tr>
	<?php } ?>
</table>

<?php pie(); ?>