<?php

require_once('../conexion.php');

encabezado('Ingreso de Unidades');

$sql = 'SELECT *
	FROM examenes
	ORDER BY id_examen';
$list = $db->sql_rowset($sql);

$form = array(
	'' => array(
		'examen' => array(
			'type' => 'text',
			'value' => 'Unidad'
		),
		'observacion' => array(
			'type' => 'text',
			'value' => 'Observaci&oacute;n'
		),
		'status' => array(
			'type' => 'select',
			'show' => 'Status',
			'value' => array(
				'Alta' => 'Alta',
				'Baja' => 'Baja'
			)
		)
	)
);

?>

<form class="form-horizontal" action="cod_examenes.php" method="post">
	<?php build($form); submit(); ?>
</form>

<div class="h"><h3>Visualizaci&oacute;n de tiempos</h3></div>

<table class="table table-striped">
	<thead>
		<tr>
			<td>Unidad</td>
			<td>Status</td>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($list as $row) { ?>
		<tr>
			<td><a href="../mantenimientos/examen/tiempo.php?id_examen=<?php echo $row->id_examen; ?>"><?php echo $row->examen; ?></a></td>
			<td><?php echo $row->status; ?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>

<?php pie(); ?>