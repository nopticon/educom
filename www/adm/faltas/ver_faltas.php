<?php

require_once('../conexion.php');

if (!isset($_REQUEST['id_falta'])) {
	redirect('index');
}

$id_falta = $_REQUEST['id_falta'];

$sql = 'SELECT *
	FROM alumno a, faltas f
	WHERE f.id_falta = ?
		AND a.id_alumno = f.id_alumno';
$list = $db->sql_rowset($db->__prepare($sql, $id_falta));

encabezado('&Uacute;ltima Falta del alumno: ' . $list[0]->nombre_alumno);

?>

<table width="100%">
	<tr>
		<td width="20%">Carn&eacute;:</td>
		<td><?php echo $list[0]->carne; ?></td>
	</tr>
	<tr>
		<td>Nombre:</td>
		<td><?php echo $list[0]->apellido . ', ' . $list[0]->nombre_alumno; ?></td>
	</tr>
	<tr>
		<td>Fecha de Ingreso:</td>
		<td class="text2"><?php echo $list[0]->fecha_falta; ?></td>
	</tr>
	<tr>
		<td>&nbsp;</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td><strong>Falta Acad&eacute;mica:</strong></td>
		<td>&nbsp;</td>
	</tr>
	<?php foreach ($list as $row) { ?>
	<tr>
		<td colspan="2"><?php echo $row->falta; ?></td>
	</tr>
	<?php } ?>
</table>

<?php pie(); ?>