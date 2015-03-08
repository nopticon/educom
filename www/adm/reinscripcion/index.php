<?php

require_once('../conexion.php');

encabezado('Re-Inscripci&oacute;n de Alumnos');

$anio = date('Y');
$status = 'ReInscrito';

$sql = 'SELECT r.fecha_reinscripcion, a.carne, a.nombre_alumno, a.apellido, g.nombre, s.nombre_seccion, a.sexo
	FROM reinscripcion r, alumno a, grado g, secciones s
	WHERE r.id_alumno = a.id_alumno
		AND r.id_grado = g.id_grado
		AND s.id_seccion = r.id_seccion
		AND anio = ?
		AND r.status = ?
	ORDER BY r.id_reinscripcion DESC LIMIT 50';
$rowset = $db->sql_rowset($db->__prepare($sql, $anio, $status));

$form = array(
	array(
		'carne' => array(
			'type' => 'input',
			'value' => 'Carn&eacute;'
		)
	)
);

?>

<div class="small-box">
	<form class="form-horizontal" action="reinscripcion.php" method="post"><?php build($form); submit(); ?></form>
</div>

<br />
<div class="h"><h3>Re-inscripciones recientes</h3></div>

<div id="list"></div>

<script type="text/javascript">
var target, grid;

<?php echo 'var kdata = ' . json_encode($rowset) . ';'; ?>

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
		columns: [ {
			field: "fecha_reinscripcion",
			title: "Fecha",
			width: 125,
		} , {
			field: "carne",
			title: "Carne",
			width: 125
		} , {
			field: "nombre_alumno",
			title: 'Nombre',
			encoded: false
		} , {
			field: "apellido",
			title: 'Apellidos',
			encoded: false
		} , {
			field: "nombre",
			title: "Grado",
			width: 150
		} , {
			field: "nombre_seccion",
			title: 'Seccion',
			width: 100
		} , {
			field: 'sexo',
			title: 'Genero',
			width: 100
		} ]
	});
});
</script>

<?php pie(); ?>