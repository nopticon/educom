<?php

require_once('../conexion.php');

encabezado('Ingreso de Cursos para Grado');

$sql = 'SELECT *
	FROM areas_cursos
	ORDER BY rel_order';
$areas_cursos = $db->sql_rowset($sql);

$sql = 'SELECT *
	FROM grado
	WHERE status = ?
	ORDER BY grade_order';
$grado = $db->sql_rowset(sql_filter($sql, 'Alta'));

$sql = 'SELECT *
	FROM catedratico
	ORDER BY nombre_catedratico, apellido';
$catedratico = $db->sql_rowset($sql);

$sql = 'SELECT *
	FROM cursos c, grado g, catedratico x
	WHERE c.id_grado = g.id_grado
		AND x.id_catedratico = c.id_catedratico
		AND g.status = ?
	ORDER BY c.id_grado';
$relacion = $db->sql_rowset(sql_filter($sql, 'Alta'));

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
			'show' => 'Grado',
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

<div class="h"><h3>Cursos - Grados - Profesores</h3></div>

<table class="table table-striped">
	<thead>
		<tr>
			<td>Curso</td>
			<td>Grado</td>
			<td>Capacidad</td>
			<td>Catedr&aacute;tico</td>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($relacion as $row) { ?>
		<tr>
			<td><?php echo $row->nombre_curso; ?></td>
			<td align="center"><?php echo $row->nombre; ?></td>
			<td align="center"><?php echo $row->capacidad; ?></td>
			<td><?php echo $row->nombre_catedratico . ' ' . $row->apellido; ?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>

<?php pie(); ?>