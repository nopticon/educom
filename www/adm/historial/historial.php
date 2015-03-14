<?php

require_once('../conexion.php');

encabezado('Historial de Alumno');

$carne = $_REQUEST['carne'];

$sql = 'SELECT *
	FROM alumno
	WHERE carne = ?';
if (!$alumno = $db->sql_fieldrow(sql_filter($sql, $carne))) {
	redirect(a('historial/index.php'));
}

$sql = 'SELECT *
	FROM reinscripcion r, alumno a, grado g, secciones s
	WHERE r.id_alumno = a.id_alumno
		AND r.id_grado = g.id_grado
		AND s.id_seccion = r.id_seccion
		AND s.id_grado = r.id_grado
		AND r.carne = ?
	ORDER BY r.anio DESC';
$list = $db->sql_rowset(sql_filter($sql, $carne));

?>

<table class="table table-bordered">
	<tbody>
		<tr>
			<td width="50%">Carn&eacute;: <?php echo $alumno->carne; ?></td>
			<td width="50%"><strong>Nombre de Padres</strong></td>
		</tr>
		<tr>
			<td>Apellido: <?php echo $alumno->apellido; ?></td>
			<td>Padre: <?php echo $alumno->padre; ?></td>
		</tr>
		<tr>
			<td>Nombre: <?php echo $alumno->nombre_alumno; ?></td>
			<td>Madre: <?php echo $alumno->madre; ?></td>
		</tr>
	</tbody>
</table>

<table class="table table-striped">
	<thead>
		<tr>
			<td>A&ntilde;o</td>
			<td>Grado</td>
			<td>Encargado</td>
			<td>Compromiso</td>
			<td>Notas</td>
			<td>Certificado</td>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($list as $row) { ?>
		<tr>
			<td><?php echo $row->anio; ?></td>
			<td><?php echo $row->nombre . ' ' . $row->nombre_seccion; ?></td>
			<td><?php echo $row->encargado_reinscripcion; ?></td>
			<td>
				<a href="../reportes/compromiso.php?id_alumno=<?php echo $row->id_alumno; ?>&amp;id_grado=<?php echo $row->id_grado; ?>" target="_blank">Compromiso</a>
			</td>
			<td>
				<a href="../reportes/grado.php?id_alumno=<?php echo $row->id_alumno; ?>&amp;id_grado=<?php echo $row->id_grado; ?>" target="_blank">Ver</a>
			</td>
			<td>
				<form action="../reportes/certificaciones2.php" method="post" target="_blank">
					<input type="hidden" name="alumno" value="<?php echo $row->id_alumno; ?>" />
					<input type="hidden" name="anio" value="<?php echo $row->anio; ?>" />
					<input type="hidden" name="seccion" value="<?php echo $row->id_seccion; ?>" />
					<input name="submit" type="submit" class="btn btn-default" value="Certificado" />
				</form>
			</td>
		</tr>
		<?php } ?>
	</tbody>
</table>

<?php pie(); ?>