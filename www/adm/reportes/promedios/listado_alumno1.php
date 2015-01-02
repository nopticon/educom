<?php

require_once('../../conexion.php');

$grado = $_REQUEST['grado'];
$seccion = $_REQUEST['seccion'];
$bimestre = $_REQUEST['examen'];
$anio = $_REQUEST['anio'];

encabezado('Promedio de Alumnos');

$sql = 'SELECT *
	FROM grado g, secciones s
	WHERE g.id_grado = ?
		AND s.id_seccion = ?
		AND g.id_grado = s.id_grado';
$grado_seccion = $db->sql_fieldrow($db->__prepare($sql, $grado, $seccion));

$sql = 'SELECT *
	FROM examenes
	WHERE id_examen = ?';
$examenes = $db->sql_fieldrow($db->__prepare($sql, $bimestre));

$sql = "SELECT *, AVG(n.nota) AS promedio
	FROM alumno a, grado g, reinscripcion r, notas n, cursos c
	WHERE r.id_grado = ?
		AND r.id_seccion = ?
		AND r.anio = ?
		AND n.id_bimestre = ?

		AND g.id_grado = r.id_grado
		AND r.id_alumno = a.id_alumno
		AND n.id_alumno = r.id_alumno
		AND n.id_grado = r.id_grado
		AND c.id_curso = n.id_curso
	GROUP BY a.id_alumno
	ORDER BY promedio DESC";
$list = $db->sql_rowset($db->__prepare($sql, $grado, $seccion, $anio, $bimestre));

?>

<table width="100%">
	<tr>
		<td width="145" class="a_center"><img src="/public/images/logo.jpg" width="110" height="117" /></td>
		<td>
			Nombre del Grado: <?php echo $grado_seccion->nombre; ?><br />
			Secci&oacute;n: <?php echo $grado_seccion->nombre_seccion; ?><br />
			Unidad: <?php echo $examenes->examen; ?><br />
			Total de alumnos: <?php echo count($list); ?>
		</td>
	</tr>
</table>

<br />
<table width="100%">
	<thead>
		<td width="5%">No.</td>
		<td width="20%">Carn&eacute;</td>
		<td>Apellido</td>
		<td>Nombre</td>
		<td width="20%">Promedio</td>
	</thead>

	<?php

	foreach ($list as $i => $row) {
		$valor = number_format(round($row->promedio, 2), 2);

		$class_name = ($valor >= 60) ? 'normal' : 'highlight';

	?>
	<tr class="<?php echo $class_name; ?>">
		<td class="a_center"><?php echo ($i + 1); ?></td>
		<td><?php echo $row->carne; ?></td>
		<td><?php echo $row->apellido; ?></td>
		<td><?php echo $row->nombre_alumno; ?></td>
		<td class="a_center"><?php echo $valor; ?></td>
	</tr>
	<?php } ?>
</table>

<br />
<div class="a_center"><?php echo str_repeat('_', 35); ?><br />Vo.Bo. Director</div>

<?php pie(); ?>