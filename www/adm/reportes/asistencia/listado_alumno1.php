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

Grado: <?php echo $grado_seccion->nombre; ?><br />
Secci&oacute;n: <?php echo $grado_seccion->nombre_seccion; ?>

<br /><br />
<table width="90%" align="center" border="1" cellpadding="5" style="border-collapse:collapse;">
	<?php

	$i = 0;
	foreach ($list as $row) {

	?>
	<tr>
		<td><?php echo $row->carne; ?></td>
		<td><?php echo $row->apellido . ', ' . $row->nombre_alumno; ?></td>
		<td width="25%">&nbsp;</td>
	</tr>
	<?php $i++; } ?>
</table>

<br />
<div class="a_center">Total de alumnos: <?php echo $i; ?></div>

<br /><br />
<div class="a_center">_______________________________________________<br />Vo.Bo. Director</div>

<?php pie(); ?>