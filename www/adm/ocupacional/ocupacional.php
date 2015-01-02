<?php

require_once('../conexion.php');

$grado = $_REQUEST['grado'];
$seccion = $_REQUEST['seccion'];
$anio = $_REQUEST['anio'];

$sql = 'SELECT *
	FROM grado g, secciones s
	WHERE g.id_grado = ?
		AND s.id_seccion = ?
		AND g.id_grado = s.id_grado';
$grado_seccion = $db->sql_fieldrow($db->__prepare($sql, $grado, $seccion));

$sql = 'SELECT *
	FROM alumno a, grado g, reinscripcion r
	WHERE r.id_alumno = a.id_alumno
		AND g.id_grado = r.id_grado
		AND r.id_grado = ?
		AND r.id_seccion = ?
		AND r.anio = ?
	ORDER BY a.apellido, a.nombre_alumno ASC';
$list = $db->sql_rowset($db->__prepare($sql, $grado, $seccion, $anio));

$sql = 'SELECT *
	FROM ocupacion_alumno';
$ocup = $db->sql_rowset($sql, 'id_alumno', 'id_ocupacion');

$sql = 'SELECT *
	FROM area_ocupacional';
$area_ocupacional = $db->sql_rowset($sql);

encabezado('Asignaci&oacute;n de Areas Ocupacionales');

?>

<table width="100%">
	<tr>
		<td width="58">&nbsp;</td>
		<td width="681" class="Estilo6" style="font-size: 12px">Nombre del Grado: <?php echo $grado_seccion->nombre . '<br/> <br/> Secci&oacute;n: ', $grado_seccion->nombre_seccion; ?></td>
		<td width="23" align="center"><a href="index.php">Inicio</a></td>
	</tr>
</table>

<br />
<?php if ($list) { ?>
<form id="form1" name="form1" method="post" action="cod_act.php">
	<table width="100%">
		<?php foreach ($list as $row) { ?>
		<tr>
			<td width="25%" align="center"><?php echo $row->carne; ?></td>
			<td width="50%"><?php echo $row->apellido . ', ' . $row->nombre_alumno; ?></td>
			<td width="25%">
				<select name="nombre_ocupacion[<?php echo $row->id_alumno; ?>]"><option value=""></option>
					<?php

					foreach ($area_ocupacional as $area_row) {
						$sel = (isset($ocup[$row->id_alumno]) && $ocup[$row->id_alumno] == $area_row->id_ocupacion);

						echo '<option value="' . $area_row->id_ocupacion . '"' . ($sel ? ' selected="selected"' : '') . '>' . $area_row->nombre_ocupacion . '</option>';
					}

					?>
				</select>
				</td>
		</tr>

		<?php
		}

		?>
		<tr>
			<td colspan="3" align="center"><input type="submit" name="Submit" value="Guardar" /></td>
		</tr>
		<tr>
			<td colspan="3">Total de alumnos: <?php echo count($list); ?></td>
		</tr>
	</table>
</form>
<?php } else { ?>
<div align="center">No se encuentran alumnos.</div>
<?php } ?>

<?php pie(); ?>