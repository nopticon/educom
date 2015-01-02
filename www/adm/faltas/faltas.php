<?php

require_once('../conexion.php');

$carne = $_REQUEST['carne'];

$sql = 'SELECT *
	FROM alumno
	WHERE carne = ?';
if (!$alumno = $db->sql_fieldrow($db->__prepare($sql, $carne))) {
	redirect('index.php');
}

encabezado('Ingreso de Faltas Acad&eacute;micas');

?>

<form action="cod_falta.php" method="post" name="formulario" id="formulario">
	<input name="guardar" type="hidden" value="1"  />
    <input name="carne" type="hidden" value="<?php echo $alumno->carne; ?>"  />
	<input name="id_alumno" type="hidden" id="id_alumno" value="<?php echo $alumno->id_alumno; ?>" />
	<input name="carnet" type="hidden" id="carnet" value="<?php echo $alumno->carne; ?>" />

	<table width="100%">
		<tr>
			<td width="25%" class="Estilo6">Datos Alumno:</td>
			<td width="75%">&nbsp;</td>
		</tr>
		<tr>
			<td class="text1" align="right">Carn&eacute;:</td>
			<td><?php echo $alumno->carne; ?></td>
		</tr>
		<tr>
			<td class="text1" align="right">Nombre:</td>
			<td><?php echo $alumno->nombre_alumno; ?></td>
		</tr>
		<tr>
			<td class="text1" align="right">Apellido:</td>
			<td><?php echo $alumno->apellido; ?></td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td class="Estilo6">Datos Padres:</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td class="text1" align="right">Padre:</td>
			<td><?php echo $alumno->padre; ?></td>
		</tr>
		<tr>
			<td class="text1" align="right">Madre:</td>
			<td><?php echo $alumno->madre; ?></td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
		<tr>
			<td class="Estilo6">Ingresar falta:</td>
			<td>&nbsp;</td>
		</tr>
		<tr>
			<td class="text1" align="right">Tipo:</td>
			<td>
				<select name="tipo_falta" id="tipo_falta">
					<option value="Baja">Baja</option>
					<option value="Media">Media</option>
					<option value="Alta">Alta</option>
				</select>
			</td>
		</tr>
		<tr>
			<td class="text1" align="right">Falta Acad&eacute;mica:</td>
			<td><textarea name="falta" cols="60" id="falta"></textarea></td>
		</tr>
		<tr>
			<td colspan="2">&nbsp;</td>
		</tr>
	</table>

	<div align="center"><?php submit(); ?></div>
</form>

<?php pie(); ?>