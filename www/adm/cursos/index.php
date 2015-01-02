<?php

require_once('../conexion.php');

encabezado('Ingreso de Cursos para Grado');

$sql = 'SELECT *
	FROM areas_cursos';
$areas_cursos = $db->sql_rowset($sql);

$sql = 'SELECT *
	FROM grado
	WHERE status = ?';
$grado = $db->sql_rowset($db->__prepare($sql, 'Alta'));

$sql = 'SELECT *
	FROM catedratico';
$catedratico = $db->sql_rowset($sql);

$sql = 'SELECT *
	FROM cursos c, grado g, catedratico x
	WHERE c.id_grado = g.id_grado
		AND x.id_catedratico = c.id_catedratico
		AND g.status = ?
	ORDER BY c.id_grado';
$relacion = $db->sql_rowset($db->__prepare($sql, 'Alta'));

$form = array(
	array(
		'areas_cursos' => array(
			'type' => 'select',
			'show' => '&Aacute;reas',
			'value' => array()
		),
		'curso' => array(
			'type' => 'text',
			'value' => 'Nombre de Curso'
		),
		'capacidad' => array(
			'type' => 'text',
			'value' => 'Capacidad'
		),
		'grado' => array(
			'type' => 'select',
			'show' => 'Relacionar a grado',
			'value' => array()
		),
		'catedratico' => array(
			'type' => 'select',
			'show' => 'Catedr&aacute;tico',
			'value' => array()
		),
	)
);

foreach ($areas_cursos as $row) {
	$form[0]['areas_cursos']['value'][$row->id_area] = $row->nombre_area;
}

foreach ($grado as $row) {
	$form[0]['grado']['value'][$row->id_grado] = $row->nombre;
}

foreach ($catedratico as $row) {
	$form[0]['catedratico']['value'][$row->id_catedratico] = $row->nombre_catedratico . ' ' . $row->apellido;
}

?>

<form class="form-horizontal" action="cod_cursos.php" method="post">
	<?php build($form); submit(); ?>
</form>

<h6>Relaci&oacute;n de Cursos - Grados - Profesores</h6>

<table width="100%">
	<thead>
		<td>Curso</td>
		<td>Grado</td>
		<td>Capacidad</td>
		<td>Catedr&aacute;tico</td>
	</thead>

	<?php foreach ($relacion as $row) { ?>
	<tr>
		<td><?php echo $row->nombre_curso; ?></td>
		<td align="center"><?php echo $row->nombre; ?></td>
		<td align="center"><?php echo $row->capacidad; ?></td>
		<td><?php echo $row->nombre_catedratico . ' ' . $row->apellido; ?></td>
	</tr>
	<?php } ?>
</table>

<?php pie(); ?>