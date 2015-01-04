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

encabezado('Historial de Faltas ' . date('Y'));

?>

<div class="a_right"><a href="/adm/faltas/faltas_alumnos.php">Regresar</a><br /><br /></div>

<table width="100%" class="tr_x1">
	<tr>
		<td width="127" class="text1">Carn&eacute;:</td>
		<td width="325" class="textred"><?php echo $alumno->carne; ?></td>
	</tr>
	<tr>
		<td>Nombres y Apellidos:</td>
		<td class="text2"><?php echo $alumno->nombre_alumno . ' ' . $alumno->apellido; ?></td>
	</tr>

	<tr>
  		<td>Grado:</td>
		<td class="text2"><?php echo $alumno->nombre; ?></td>
	</tr>
	<tr>
		<td>Encargado:</td>
		<td class="text2"><?php echo $alumno->encargado_reinscripcion; ?></td>
	</tr>
</table>

<br /><br />
<table width="100%">
	<thead>
		<td width="15%">Fecha de Ingreso</td>
		<td>Descripci&oacute;n</td>
		<td width="10%">Tipo</td>
	</thead>

	<?php foreach ($list as $row) { ?>
	<tr class="tr_x1">
		<td align="center"><?php echo $row->fecha_falta; ?></td>
		<td><?php echo $row->falta; ?></td>
		<td align="center"><?php echo $row->tipo_falta; ?></td>
	</tr>
	<?php } ?>
</table>

<?php pie(); ?>