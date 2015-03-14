<?php

require_once('../conexion.php');

encabezado('Busqueda Alumno', '', false);

$nombre = request_var('nombre', '');
$apellido = request_var('apellido', '');

if ($nombre || $apellido) {
	// $sql = "SELECT id_alumno, carne, apellido, nombre_alumno
	// 	FROM alumno
	// 	WHERE nombre_alumno LIKE '%??%'
	// 		AND apellido LIKE '%??%'
	// 	ORDER BY apellido, nombre_alumno";
	// $alumnos = $db->sql_rowset(sql_filter($sql, $nombre, $apellido));
	$sql = 'SELECT id_alumno, carne, apellido, nombre_alumno
		FROM alumno a
		INNER JOIN _members m ON m.user_id = a.id_member
		WHERE m.username_base LIKE ?
			AND m.username_base LIKE ?
		ORDER BY a.apellido, a.nombre_alumno';

	// _pre(sql_filter($sql, '%' . $nombre . '%', '%' . $apellido . '%'), true);

	$alumnos = sql_rowset(sql_filter($sql, '%' . $nombre . '%', '%' . $apellido . '%'));

	// _pre($alumnos, true);
}

$form = array(
	array(
		'nombre' => array(
			'type' => 'input',
			'value' => 'Nombres'
		),
		'apellido' => array(
			'type' => 'input',
			'value' => 'Apellido'
		)
	)
);

?>

<form class="form-horizontal" action="index.php" method="post">
	<?php build($form); submit(); ?>
</form>

<?php

if ($nombre || $apellido) {
	if ($alumnos) {
?>
<br />
<table class="table table-striped">
	<thead>
		<tr>
			<td>#</td>
			<td>Carn&eacute;</td>
			<td>Apellido</td>
			<td>Nombre</td>
		</tr>
	</thead>
	<tbody>
		<?php foreach ($alumnos as $i => $row) { ?>
		<tr>
			<td><?php echo ($i + 1); ?></td>
			<td><?php echo $row->carne; ?></td>
			<td><?php echo $row->nombre_alumno; ?></td>
			<td><?php echo $row->apellido; ?></td>
			<td><a href="dato_alumno.php?id_alumno=<?php echo $row->id_alumno; ?>">Editar</a></td>
		</tr>
		<?php } ?>
	</tbody>
</table>
<?php
	} else {
		echo '<p class="bg-danger">No se encuentran alumnos relacionados a su b&uacute;squeda.</p>';
	}
}

pie();