<?php

require_once('../conexion.php');

$seccion = $_REQUEST['grado'];
$curso = $_REQUEST['curso'];
$examen = $_REQUEST['examen'];
$anio = $_REQUEST['anio'];

$sql = 'SELECT id_grado, nombre_seccion
	FROM secciones
	WHERE id_seccion = ?';
if (!$gradoar = $db->sql_fieldrow($db->__prepare($sql, $seccion))) {
	redirect('index.php');
}

$grado = $gradoar->id_grado;

$sql = 'SELECT *
	FROM grado
	WHERE id_grado = ?';
if (!$_grado = $db->sql_fieldrow($db->__prepare($sql, $grado))) {
	redirect('index.php');
}

$sql = 'SELECT *
	FROM cursos
	WHERE id_curso = ?';
if (!$_curso = $db->sql_fieldrow($db->__prepare($sql, $curso))) {
	redirect('index.php');
}

$sql = 'SELECT *
	FROM examenes
	WHERE id_examen = ?';
if (!$_examen = $db->sql_fieldrow($db->__prepare($sql, $examen))) {
	redirect('index.php');
}

$sql = 'SELECT *
	FROM alumno a, grado g, reinscripcion r
	WHERE g.id_grado = ?
		AND r.id_seccion = ?
		AND r.anio = ?
		AND r.id_grado = g.id_grado
		AND r.id_alumno = a.id_alumno
	ORDER BY a.apellido ASC';
if (!$list = $db->sql_rowset($db->__prepare($sql, $grado, $seccion, $anio))) {
	redirect('index.php');
}

encabezado('Ingreso de notas');

?>

<form class="form-horizontal" action="./cod_notas.php" method="post">
	<input name="grado" type="hidden" id="grado" value="<?php echo $grado; ?>" />
	<input name="curso" type="hidden" id="curso" value="<?php echo $curso; ?>" />
	<input name="examen" type="hidden" id="examen" value="<?php echo $_examen->id_examen; ?>" />

	<table width="100%">
		<tr>
			<td width="50%" class="a_right">Grado:</td>
			<td width="50%"><?php echo $_grado->nombre . ' - secci&oacute;n: ' . $gradoar->nombre_seccion; ?></td>
		</tr>
		<tr>
			<td class="a_right">Curso:</td>
			<td><?php echo $_curso->nombre_curso; ?></td>
		</tr>
		<tr>
			<td class="a_right">Unidad:</td>
			<td><?php echo $_examen->examen; ?></td>
		</tr>
		<tr>
			<td class="a_right">A&ntilde;o:</td>
			<td><?php echo $anio; ?></td>
		</tr>
	</table>

	<table width="100%" align="center">
		<thead>
			<td width="20%">Carn&eacute;</td>
			<td>Apellidos</td>
			<td>Nombres</td>
			<td width="20%">Nota</td>
		</thead>

		<?php foreach ($list as $row) { ?>
		<tr>
			<td align="center"><?php echo $row->carne; ?></td>
			<td><?php echo $row->apellido; ?></td>
			<td><?php echo $row->nombre_alumno; ?></td>
			<td align="center"><?php

			$sql = 'SELECT *
				FROM notas
				WHERE id_alumno = ?
					AND id_grado = ?
					AND id_curso = ?
					AND id_bimestre = ?';
			if ($cada_nota = $db->sql_field($db->__prepare($sql, $row->id_alumno, $grado, $curso, $examen), 'nota', 0)) {
				echo $cada_nota;
			} else {
				echo '<input name="nota[' . $row->id_alumno . ']" type="text" size="5" />';
			}

			?></td>
		</tr>
		<?php } ?>
	</table>

	<br />
	<?php submit('Guardar notas'); ?>
</form>

<?php pie(); ?>