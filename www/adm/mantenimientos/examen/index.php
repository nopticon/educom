<?php

require_once('../../conexion.php');

encabezado('Modificaci&oacute;n de Unidades');

$sql = 'SELECT *
	FROM examenes';
$examenes = $db->sql_rowset($sql);

?>

<table class="table table-striped">
	<thead>
		<tr>
			<td>Nombre de unidad</td>
			<td>Status</td>
		</tr>
	</thead>
	</tbody>
		<?php foreach ($examenes as $row) { ?>
		<tr>
			<td><a href="tiempo.php?id_examen=<?php echo $row->id_examen; ?>"><?php echo $row->examen; ?></a></td>
			<td class="a_center"><?php echo $row->status; ?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>

<?php pie(); ?>