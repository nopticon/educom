<?php

require_once('../conexion.php');

encabezado('Busqueda Alumno', '', false);

$nombre = isset($_REQUEST['nombre']) ? $_REQUEST['nombre'] : '';
$apellido = isset($_REQUEST['apellido']) ? $_REQUEST['apellido'] : '';

if ($nombre || $apellido) {
	$sql = "SELECT id_alumno, carne, apellido, nombre_alumno
		FROM alumno
		WHERE nombre_alumno LIKE '%??%'
			AND apellido LIKE '%??%'
		ORDER BY apellido, nombre_alumno";
	$alumnos = $db->sql_rowset($db->__prepare($sql, $nombre, $apellido));
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
<div id="list"></div>

<script type="text/javascript">
var target, grid;

<?php echo 'var kdata = ' . json_encode($alumnos) . ';'; ?>

$(function() {
	target = $('#list');
	grid = target.kendoGrid({
		dataSource: {
			data: kdata,
			pageSize: 10
		},
		sortable: true,
		pageable: {
			pageSizes: true
		},
		columns: [
			{ field: "carne", title: "Carne", width: 125 },
			{ field: "nombre_alumno", title: 'Nombre', encoded: false },
			{ field: "apellido", title: 'Apellidos', encoded: false },
			{ title: 'Editar', template: t_editar, width: 75 }
		]
	});
});

function t_editar(a) {
	return '<a href="dato_alumno.php?id_alumno=' + a.id_alumno + '"><img src="/public/images/configuration.png" width="20" /></a>';
}
</script>
<?php
	} else {
		echo 'No se encuentran alumnos relacionados a su b&uacute;squeda.';
	}
}

pie();