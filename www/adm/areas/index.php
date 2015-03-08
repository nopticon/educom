<?php

require_once('../conexion.php');

encabezado('Ingreso de &Aacute;reas');

$sql = 'SELECT *
	FROM areas_cursos';
$list = $db->sql_rowset($sql);

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

<form class="form-horizontal" action="cod_area.php" method="post">
	<?php build($form); submit(); ?>
</form>

<div class="h"><h3>Lista de &Aacute;reas</h3></div>

<table class="table table-striped">
	<tbody>
		<?php foreach ($list as $row) { ?>
		<tr>
			<td><?php echo $row->nombre_area; ?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>

<?php pie(); ?>