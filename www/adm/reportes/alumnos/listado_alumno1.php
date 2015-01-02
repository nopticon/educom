<?php

require_once('../../conexion.php');

encabezado('Listado de Alumnos en Grado', '', false);

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

<br />
<table width="100%">
	<tr>
		<td width="58">&nbsp;</td>
		<td width="681">
			Grado: <strong><?php echo $grado_seccion->nombre; ?></strong><br />
			Secci&oacute;n: <strong><?php echo $grado_seccion->nombre_seccion; ?></strong><br />
			A&ntilde;o: <strong><?php echo $anio; ?></strong><br />
			Asignatura: _______________________________________________<br />
			Catedr&aacute;tico: _______________________________________________<br />
			Trimestres: <strong>1er _____ 2do _____ 3ro _____</strong>
		</td>
		<td width="23" align="center"><img src="/public/images/logo.jpg" width="110" height="117" /></td>
	</tr>
</table>

<br />
<table width="90%" align="center" border="1" cellpadding="5" style="border-collapse:collapse;">
	<tr>
		<td width="10%">&nbsp;</td>
		<td>&nbsp;</td>
		<td width="5%">Afect.</td>
		<td width="5%">Cong/Psic</td>
		<td width="5%">TOTAL</td>
	</tr>

	<?php

	$i = 0;
	foreach ($list as $row) {
	?>
	<tr>
		<td><?php echo $row->carne; ?></td>
		<td><?php echo $row->apellido . ', ' . $row->nombre_alumno; ?></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<?php $i++; } ?>
</table>

<br />
<div class="a_center">Total de alumnos: <?php echo $i; ?></div>

<br /><br />
<div class="a_center">_______________________________________________<br />Firma de catedr&aacute;tico</div>

<?php pie(); ?>