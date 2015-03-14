<?php

require_once('../conexion.php');

$seccion = request_var('grado', 0);
$curso = request_var('curso', 0);
$examen = request_var('examen', 0);
$anio = request_var('anio', 0);

encabezado('Edici&oacute;n de Calificaciones');

$sql = 'SELECT id_grado, nombre_seccion
	FROM secciones
	WHERE id_seccion = ?';
if (!$secciones = $db->sql_fieldrow($db->__prepare($sql, $seccion))) {
	exit;
}

$grado = $secciones->id_grado;

$sql = 'SELECT *
	FROM grado
	WHERE id_grado = ?';
if (!$grados = $db->sql_fieldrow($db->__prepare($sql, $grado))) {
	exit;
}

$sql = 'SELECT *
	FROM cursos
	WHERE id_curso = ?';
$cursos = $db->sql_fieldrow($db->__prepare($sql, $curso));

$sql = 'SELECT *
	FROM examenes
	WHERE id_examen = ?';
$examenes = $db->sql_fieldrow($db->__prepare($sql, $examen));

$sql = 'SELECT *
	FROM alumno a, grado g, reinscripcion r
	WHERE r.id_grado = g.id_grado
		AND r.id_alumno = a.id_alumno
		AND g.id_grado = ?
		AND r.id_seccion = ?
		AND r.anio = ?
	ORDER BY a.apellido ASC';
$reinscripcion = $db->sql_rowset($db->__prepare($sql, $grado, $seccion, $anio));

?>

<table class="table table-bordered">
	<tr>
		<td width="50%">Grado: <?php echo $grados->nombre . ' ' . $secciones->nombre_seccion; ?></td>
		<td>Unidad: <?php echo $examenes->examen; ?></td>
	</tr>
	<tr>
		<td>Curso: <?php echo $cursos->nombre_curso; ?></td>
		<td>A&ntilde;o: <?php echo $anio; ?></td>
	</tr>
</table>

<form action="./cod_notas.php" method="post">
	<input name="grado" type="hidden" value="<?php echo $grado; ?>" />
	<input name="curso" type="hidden" value="<?php echo $curso; ?>" />
	<input name="examen" type="hidden" value="<?php echo $examenes->id_examen; ?>" />

	<table class="table table-striped">
		<thead>
			<td>Carn&eacute;</td>
			<td>Apellidos</td>
			<td>Nombres</td>
			<td>Nota</td>
		</thead>
		<tbody>
			<?php

			foreach ($reinscripcion as $row) {
				$sql = 'SELECT *
					FROM notas
					WHERE id_alumno = ?
						AND id_grado = ?
						AND id_curso = ?
						AND id_bimestre = ?';
				$cada_nota = $db->sql_field($db->__prepare($sql, $row->id_alumno, $grado, $curso, $examen), 'nota', false);

			?>
			<tr>
				<td><?php echo $row->carne; ?></td>
				<td><?php echo $row->apellido; ?>
				<td><?php echo $row->nombre_alumno; ?></td>
				<td><?php

				echo '<input class="form-control" name="nota[' . $row->id_alumno . ']" value="' . (($cada_nota !== false) ? $cada_nota : '') . '" type="text" size="5" />';

				?></td>
			</tr>

			<?php } ?>
		</tbody>
	</table>

	<div class="text-center"><input class="btn btn-danger" type="submit" name="submit" value="Guardar Notas" /></div>
</form>

<?php pie(); ?>