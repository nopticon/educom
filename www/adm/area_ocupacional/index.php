<?php

require_once('../conexion.php');

encabezado('Ingreso de &Aacute;reas Ocupacionales');

$sql = 'SELECT *
	FROM area_ocupacional';
$rowset = $db->sql_rowset($sql);

$form = array(
	array(
		'area' => array(
			'type' => 'input',
			'value' => 'Nombre de &Aacute;rea'
		),
		'observacion' => array(
			'type' => 'textarea',
			'value' => 'Observaci&oacute;n'
		)
	)
);

?>

<form class="form-horizontal" action="cod_ocupacional.php" method="post">
	<?php build($form); submit(); ?>
</form>

<div class="h"><h3>Lista de &Aacute;reas</h3></div>

<table class="table table-striped">
	<tbody>
		<?php foreach ($rowset as $row) { ?>
		<tr>
			<td><?php echo $row->nombre_ocupacion; ?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>

<?php pie(); ?>