<?php

require_once('../../conexion.php');

encabezado('Asistencia de alumnos', '', false);

$grado = $_REQUEST['grado'];
$seccion = $_REQUEST['seccion'];
$anio = $_REQUEST['anio'];

$sql = 'SELECT *
	FROM grado g, secciones s
	WHERE g.id_grado = ?
		AND s.id_seccion = ?
		AND g.id_grado = s.id_grado';
$grado_seccion = $db->sql_fieldrow($db->__prepare($sql, $grado, $seccion));

$sql = 'SELECT *
	FROM alumno a, grado g, reinscripcion r
	WHERE r.id_alumno = a.id_alumno
		AND g.id_grado = r.id_grado
		AND r.id_grado = ?
		AND r.id_seccion = ?
		AND r.anio = ?
	ORDER BY a.apellido, a.nombre_alumno ASC';
$list = $db->sql_rowset($db->__prepare($sql, $grado, $seccion, $anio));

?>

<h2>Grado: <?php echo $grado_seccion->nombre . ' ' . $grado_seccion->nombre_seccion; ?></h2>

<br />
<table class="table table-bordered">
	<tr>
		<td>Asignatura:</td>
	</tr>
	<tr>
		<td>Catedr&aacute;tico:</td>
	</tr>
</table>

<table class="table table-striped">
	<thead>
		<tr>
			<td>#</td>
			<td>Carn&eacute;</td>
			<td>Apellido</td>
			<td>Nombre</td>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($list as $i => $row) { ?>
		<tr>
			<th scope="row"><?php echo ($i + 1); ?></th>
			<td><?php echo $row->carne; ?></td>
			<td><?php echo $row->apellido; ?></td>
			<td><?php echo $row->nombre_alumno; ?></td>
		</tr>
		<?php } ?>
	</tbody>
</table>

<?php pie(); ?>