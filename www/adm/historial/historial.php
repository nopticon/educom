<?php

require_once('../conexion.php');

encabezado('Historial de Alumno');

$carne = $_REQUEST['carne'];

$sql = 'SELECT *
	FROM alumno
	WHERE carne = ?';
if (!$alumno = $db->sql_fieldrow($db->__prepare($sql, $carne))) {
	redirect('index.php');
}

$sql = 'SELECT *
	FROM reinscripcion r, alumno a, grado g, secciones s
	WHERE r.id_alumno = a.id_alumno
		AND r.id_grado = g.id_grado
		AND s.id_seccion = r.id_seccion
		AND s.id_grado = r.id_grado
		AND r.carne = ?
	ORDER BY r.anio DESC';
$list = $db->sql_rowset($db->__prepare($sql, $carne));

?>

<div class="f_left w_50">
	<table width="100%">
		<tr>
			<td width="25%" class="a_right">Datos Alumno:</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td class="a_right">Carn&eacute;:</td>
			<td><?php echo $alumno->carne; ?></td>
		</tr>
		<tr>
			<td  class="a_right">Nombre:</td>
			<td><?php echo $alumno->nombre_alumno; ?></td>
		</tr>
		<tr>
			<td class="a_right">Apellido:</td>
			<td><?php echo $alumno->apellido; ?></td>
		</tr>
	</table>
</div>

<div class="f_right w_50">
	<table width="100%">
		<tr>
			<td width="25%" class="a_right">Datos Padres:</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td class="a_right">Padre:</td>
			<td><?php echo $alumno->padre; ?></td>
		</tr>
		<tr>
			<td class="a_right">Madre:</div></td>
			<td><?php echo $alumno->madre; ?></td>
		</tr>
	</table>
</div>

<span class="clear"></span>
<h6>Historial de Grados</h6>

<table width="100%">
	<thead>
		<td width="10%">A&ntilde;o</td>
		<td width="22%">Grado</td>
		<td width="31%">Encargado</td>
		<td width="11%">Compromiso</td>
		<td width="4%">Notas</td>
		<td width="22%">Certificado</td>
	</thead>

	<?php foreach ($list as $row) { ?>
	<tr>
		<td class="a_center"><?php echo $row->anio; ?></td>
		<td><?php echo $row->nombre . ', secci&oacute;n: ' . $row->nombre_seccion; ?></td>
		<td><?php echo $row->encargado_reinscripcion; ?></td>
		<td class="a_center">
			<a href="../reportes/compromiso.php?id_alumno=<?php echo $row->id_alumno; ?>&amp;id_grado=<?php echo $row->id_grado; ?>" target="_blank"><img src="/public/images/printer.png" height="20" /></a></td>
		<td class="a_center"><a href="../reportes/grado.php?id_alumno=<?php echo $row->id_alumno; ?>&amp;id_grado=<?php echo $row->id_grado; ?> " target="_blank"><img src="/public/images/lista2.png" height="20" /></a></td>
		<td class="a_center">
			<form action="../reportes/certificaciones2.php" method="post" target="_blank">
				<input type="hidden" name="alumno" value="<?php echo $row->id_alumno; ?>" />
				<input type="hidden" name="anio" value="<?php echo $row->anio; ?>" />
				<input type="hidden" name="seccion" value="<?php echo $row->id_seccion; ?>" />
				<input name="submit" type="image" style="height: 30px;" src="/public/images/certificado.png" title="Buscar por carn&eacute;..." />
			</form>
		</td>
	</tr>
	<?php } ?>
</table>

<?php pie(); ?>