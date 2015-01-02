<?php

require_once('../conexion.php');
$grado = $_REQUEST['grado'];
$seccion = $_REQUEST['seccion'];
$anio = $_REQUEST['anio'];

$sql = 'SELECT *
	FROM grado g, secciones s
	WHERE g.id_grado = s.id_grado
		AND g.id_grado = ?
		AND s.id_seccion = ?';
$grados = $db->sql_fieldrow($db->__prepare($sql, $grado, $seccion));

$sql = 'SELECT *
	FROM alumno a, grado g, reinscripcion r
	WHERE r.id_alumno = a.id_alumno
		AND g.id_grado = r.id_grado
		AND r.id_grado = ?
		AND r.id_seccion = ?
		AND r.anio = ?
	ORDER BY a.apellido, a.nombre_alumno ASC';
$alumnos = $db->sql_rowset($db->__prepare($sql, $grado, $seccion, $anio));

encabezado('Ingreso de c&oacute;digo de alumno');

if ($grados) {
	echo 'Grado: ' . $grados->nombre . '<br />';
	echo 'Secci&oacute;n: ' . $grados->nombre_seccion . '<br />';
	echo 'Total de alumnos: ' . count($alumnos);
}

?>

<br /><br />
<form method="post" action="cod_act.php">
	<table width="100%" border="1" cellpadding="3">
		<thead>
			<td align="center" width="150">Carn&eacute;</td>
			<td align="center" width="175">C&oacute;digo de alumno</td>
			<td align="center">Nombre</td>

		</thead>
		<?php foreach ($alumnos as $row) { ?>
		<tr>
			<td align="center"><?php echo $row->carne; ?></td>
		    <td align="center"><?php

			if ($row->codigo_alumno) {
				echo '<div>' . $row->codigo_alumno . '</div>';
			} else {
				echo '<input name="textfield[' . $row->id_alumno . ']" type="text" size="25" value="' . $row->codigo_alumno . '" />';
			}

			?></td>
	      <td><?php echo $row->apellido . ', ' . $row->nombre_alumno; ?></td>
	  </tr>
		<?php } ?>
	</table>

	<br />
	<div class="a_center">
		<input type="submit" class="btn btn-primary" name="Submit" value="Guardar Codigos" />
	</div>
</form>

<?php pie(); ?>