<?php

require_once('../conexion.php');

$grado = request_var('grado', 0);
$seccion = request_var('seccion', 0);
$anio = request_var('anio', 0);

$sql = 'SELECT *
	FROM grado g, secciones s
	WHERE g.id_grado = s.id_grado
		AND g.id_grado = ?
		AND s.id_seccion = ?';
$grados = $db->sql_fieldrow(sql_filter($sql, $grado, $seccion));

$sql = 'SELECT *
	FROM alumno a, grado g, reinscripcion r
	WHERE r.id_alumno = a.id_alumno
		AND g.id_grado = r.id_grado
		AND r.id_grado = ?
		AND r.id_seccion = ?
		AND r.anio = ?
	ORDER BY a.apellido, a.nombre_alumno ASC';
$alumnos = $db->sql_rowset(sql_filter($sql, $grado, $seccion, $anio));

encabezado('Ingreso de c&oacute;digo de alumno');

if ($grados) {
	echo '<h2>Grado: ' . $grados->nombre . ' ' . $grados->nombre_seccion . '</h2>';
}

?>

<br />
<form method="post" action="cod_act.php">
	<table class="table table-striped">
		<thead>
			<tr>
				<th>#</th>
				<th width="150">Carn&eacute;</th>
				<th width="175">C&oacute;digo de alumno</th>
				<th>Nombre</th>
			</tr>
		</thead>
		<tbody>
			<?php foreach ($alumnos as $i => $row) { ?>
			<tr>
				<th scope="row"><?php echo ($i + 1); ?></th>
				<td align="center"><?php echo $row->carne; ?></td>
				<td align="center"><?php

				if ($row->codigo_alumno) {
					echo '<div>' . $row->codigo_alumno . '</div>';
				} else {
					echo '<input class="form-control" name="textfield[' . $row->id_alumno . ']" type="text" value="' . $row->codigo_alumno . '" />';
				}

				?></td>
		      <td><?php echo $row->apellido . ', ' . $row->nombre_alumno; ?></td>
		  	</tr>
			<?php } ?>
		</tbody>
	</table>

	<div class="text-center">
		<input type="submit" class="btn btn-danger" name="submit" value="Guardar C&oacute;digos" />
	</div>
</form>

<?php pie(); ?>