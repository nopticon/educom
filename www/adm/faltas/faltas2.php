<?php

require_once('../conexion.php');

$carne = $_REQUEST['carne1'];
$carne_sub = substr($carne, 5);

// $anio = date('Y');

$sql = 'SELECT *
	FROM reinscripcion r, grado g, alumno a
	WHERE  r.carne = ?
		AND g.id_grado = r.id_grado
		AND r.id_alumno = a.id_alumno';
if (!$alumno = $db->sql_fieldrow($db->__prepare($sql, $carne))) {
	redirect('index.php');
}

$sql = 'SELECT *
	FROM alumno a, faltas f
	WHERE a.id_alumno = f.id_alumno
		AND f.id_alumno = ?
	ORDER BY f.fecha_falta DESC';
$list = $db->sql_rowset($db->__prepare($sql, $carne_sub));

encabezado('Informaci&oacute;n Faltas del ' . date('Y'));

?>

<table width="100%">
	<tr>
		<td width="111">&nbsp;</td>
		<td width="127" class="text1"><div align="right">Carn&eacute;:</div></td>
		<td width="325" class="textred"><?php echo $alumno->carne; ?></td>
		<td width="73" class="text1">&nbsp;</td>
		<td width="146">&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><div align="right" class="text1">Nombres y Apellidos: </div></td>
		<td class="text2"><?php echo $alumno->nombre_alumno . ', ' . $alumno->apellido; ?></td>
		<td>&nbsp;</td>
		<td class="text2">&nbsp;</td>
	</tr>

	<tr>
		<td>&nbsp;</td>
  <td><div align="right" class="text1">Grado:</div></td>
		<td class="text2"><?php echo $alumno->nombre; ?></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td><div align="right" class="text1">Encargado:</div></td>
		<td class="text2"><?php echo $alumno->encargado_reinscripcion; ?></td>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
</table>

<br />
<table width="100%">
	<thead>
		<td width="15%">Fecha de Ingreso</td>
		<td>Descripci&oacute;n</td>
		<td width="10%">Tipo</td>
	</thead>

	<?php foreach ($list as $row) { ?>
	<tr>
		<td align="center"><?php echo $row->fecha_falta; ?></td>
		<td><?php echo $row->falta; ?></td>
		<td align="center"><?php echo $row->tipo_falta; ?></td>
	</tr>
	<?php } ?>
</table>

<?php pie(); ?>