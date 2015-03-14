<?php

require_once('../conexion.php');

encabezado('Alumno ingresado &eacute;xitosamente!');

//
// Alumnos nuevos
//
$sql = 'SELECT a.id_alumno, a.carne, a.fecha, a.nombre_alumno, a.apellido, g.nombre, s.nombre_seccion
	FROM alumno a, reinscripcion r, grado g, secciones s
	WHERE a.id_alumno = r.id_alumno
		AND r.id_grado = g.id_grado
		AND r.id_seccion = s.id_seccion
		AND r.anio = ?
	ORDER BY a.id_alumno DESC
	LIMIT 100';
$rowset = $db->sql_rowset($db->__prepare($sql, date('Y')));

?>

<div class="small-box">
	<a href="index.php" class="btn btn-hg btn-success">Nueva inscripcion</a>&nbsp;&nbsp;
	<a href="../reinscripcion/index.php" class="btn btn-hg btn-info">Re-Inscripci&oacute;n</a>
</div>

<br />
<h6>Lista de alumnos nuevos</h6>
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
		columns: [
			{ field: "carne", title: "Carne", width: 125 },
			{ field: "fecha", title: "Fecha", width: 125 },
			{ field: "nombre_alumno", title: 'Nombre', encoded: false },
			{ field: "apellido", title: 'Apellidos', encoded: false },
			{ field: "nombre", title: "Grado", width: 150 },
			{ field: "nombre_seccion", title: 'Seccion', width: 100 },
			{ title: 'Compromiso', template: t_compromiso, width: 100 },
			{ title: 'Editar', template: t_editar, width: 75 }
		]
	});
});

function t_compromiso(a) {
	return '<a href="../reportes/compromiso.php?id_alumno=' + a.id_alumno + '" target="_blank">Compromiso #1</a> <a href="../reportes/compromiso2.php?id_alumno=' + a.id_alumno + '" target="_blank">Compromiso #2</a>';
}

function t_editar(a) {
	return '<a href="../mantenimientos/alumnos/alumno.php?carne=' + a.carne + '&amp;Submit2=Buscar">Editar</a>';
}
</script>

<?php pie(); ?>