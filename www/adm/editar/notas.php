<?php

require_once('../conexion.php');

$seccion = $_REQUEST['grado'];
$curso = $_REQUEST['curso'];
$examen = $_REQUEST['examen'];
$anio = $_REQUEST['anio'];

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

<form action="./cod_notas.php" method="post" name="formulario" id="formulario">
	<input name="grado" type="hidden" id="grado" value="<?php echo $grado; ?>" />
	<input name="curso" type="hidden" id="curso" value="<?php echo $curso; ?>" />
	<input name="examen" type="hidden" id="examen" value="<?php echo $examenes->id_examen; ?>" />

	<table width="100%">
		<tr>
			<td width="50%" class="a_right">Grado:</td>
			<td width="50%"><?php echo $grados->nombre . ' - secci&oacute;n: ' . $secciones->nombre_seccion; ?></td>
		</tr>
		<tr>
			<td class="a_right">Curso:</td>
			<td><?php echo $cursos->nombre_curso; ?></td>
		</tr>
		<tr>
			<td class="a_right">Unidad:</td>
			<td><?php echo $examenes->examen; ?></td>
		</tr>
		<tr>
			<td class="a_right">A&ntilde;o:</td>
			<td><?php echo $anio; ?></td>
		</tr>
	</table>

	<br />

	<table width="100%">
		<thead>
			<td width="20%">Carn&eacute;</td>
			<td>Apellidos</td>
			<td>Nombres</td>
			<td width="20%">Nota</td>
		</thead>
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
			<td align="center"><?php echo $row->carne; ?></td>
			<td><?php echo $row->apellido; ?>
			<td><?php echo $row->nombre_alumno; ?></td>
			<td align="center"><?php

			echo '<input name="nota[' . $row->id_alumno . ']" value="' . (($cada_nota !== false) ? $cada_nota : '') . '" type="text" size="5" />';

			?></td>
		</tr>

		<?php } ?>
	</table>

	<br />
	<div align="center"><input class="btn btn-danger" type="submit" name="submit" value="Guardar Notas" /></div>
</form>

<?php pie(); ?>