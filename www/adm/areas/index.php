<?php

require_once('../conexion.php');

encabezado('Ingreso de &Aacute;reas');

$sql = 'SELECT *
	FROM areas_cursos';
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

<form class="form-horizontal" action="cod_area.php" method="post">
	<?php build($form); submit(); ?>
</form>

<?php if (is_array($rowset)) { ?>
<br />
<h6>Lista de &Aacute;reas</h6>

<ul>
<?php foreach ($rowset as $row) { ?>
	<li><?php echo $row->nombre_area; ?></li>
<?php } ?>
</ul>
<?php } ?>

<?php pie(); ?>