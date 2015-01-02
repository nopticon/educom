<?php

require_once('../conexion.php');

$sql = 'SELECT nombre, status
	FROM grado g
	ORDER BY id_grado';
$list = $db->sql_rowset($sql);

encabezado('Ingreso de Grados');

$form = array(
	'' => array(
		'grado' => array(
			'type' => 'text',
			'value' => 'Nombre de grado'
		),
		'status' => array(
			'type' => 'select',
			'show' => 'Estado',
			'value' => array(
				'Alta' => 'Alta',
				'Baja' => 'Baja'
			)
		),
	),
);

?>

<form class="form-horizontal" action="cod_grado.php" method="post">
	<?php build($form); submit(); ?>
</form>

<h5>Grados actuales</h5>

<table width="100%">
	<thead>
		<td width="40%">Grado</td>
		<td width="10%">Status</td>
		<td width="50%">&nbsp;</td>
	</thead>

	<?php

	foreach ($list as $row) {
	?>
	<tr>
		<td><?php echo $row->nombre; ?></td>
		<td class="a_center"><?php echo $row->status; ?></td>
		<td>&nbsp;</td>
	</tr>
	<?php } ?>
</table>

<?php pie(); ?>