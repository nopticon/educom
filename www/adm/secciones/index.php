<?php

require_once('../conexion.php');

$sql = "SELECT *
	FROM grado
	WHERE status = 'Alta'";
$grado = $db->sql_rowset($sql);

$sql = "SELECT nombre, nombre_seccion
	FROM secciones s, grado g
	WHERE g.id_grado = s.id_grado
		AND g.status = 'Alta'";
$secciones = $db->sql_rowset($sql);

encabezado('Ingreso de Secciones');

$form = array(
	'' => array(
		'grado' => array(
			'type' => 'select',
			'show' => 'Grado',
			'value' => array()
		),
		'seccion' => array(
			'type' => 'text',
			'value' => 'Secci&oacute;n'
		),
	),
);

foreach ($grado as $row) {
	$form['']['grado']['value'][$row->id_grado] = $row->nombre;
}

?>

<form class="form-horizontal" action="cod_secciones.php" method="post">
	<?php build($form); submit(); ?>
</form>

<h5>Secciones actuales</h5>

<table width="100%">
	<thead>
		<td width="40%">Grado</td>
		<td width="10%">Secci&oacute;n</td>
		<td width="50%">&nbsp;</td>
	</thead>

	<?php foreach ($secciones as $row) {
	?>
	<tr>
		<td><?php echo $row->nombre; ?></td>
		<td class="a_center"><?php echo $row->nombre_seccion; ?></td>
	</tr>
	<?php } ?>
</table>

<?php pie(); ?>