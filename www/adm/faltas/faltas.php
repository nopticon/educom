<?php

require_once('../conexion.php');

$carne = request_var('carne', '');

$sql = 'SELECT *
	FROM alumno
	WHERE carne = ?';
if (!$alumno = $db->sql_fieldrow(sql_filter($sql, $carne))) {
	location('.');
}

encabezado('Ingreso de Faltas Acad&eacute;micas');

?>

<form class="form-horizontal" action="cod_falta.php" method="post">
	<input name="carne" type="hidden" value="<?php echo $alumno->carne; ?>"  />
	<input name="id_alumno" type="hidden" id="id_alumno" value="<?php echo $alumno->id_alumno; ?>" />
	<input name="carnet" type="hidden" id="carnet" value="<?php echo $alumno->carne; ?>" />

	<table class="table table-bordered">
		<tbody>
			<tr>
				<td>Carn&eacute;</td>
				<td><?php echo $alumno->carne; ?></td>
			</tr>
			<tr>
				<td>Nombre</td>
				<td><?php echo $alumno->nombre_alumno; ?></td>
			</tr>
			<tr>
				<td>Apellido</td>
				<td><?php echo $alumno->apellido; ?></td>
			</tr>
		</tbody>
	</table>

	<div class="form-group">
		<label for="inputTipo" class="col-lg-2 control-label">Tipo de falta</label>
		<div class="col-lg-10">
			<select name="tipo_falta" id="tipo_falta">
				<option value="Baja">Baja</option>
				<option value="Media">Media</option>
				<option value="Alta">Alta</option>
			</select>
		</div>
	</div>

	<div class="form-group">
		<label for="inputFalta" class="col-lg-2 control-label">Descripci&oacute;n</label>
		<div class="col-lg-10">
			<textarea name="falta" class="form-control" id="inputFalta"></textarea>
		</div>
	</div>

	<div class="text-center"><?php submit(); ?></div>
</form>

<?php pie(); ?>