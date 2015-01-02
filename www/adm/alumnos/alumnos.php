<?php

require_once('../conexion.php');

encabezado('Lista de Alumnos');

$sql = 'SELECT a.id_alumno, a.carne, a.fecha, a.nombre_alumno, a.apellido, g.nombre, s.nombre_seccion
	FROM alumno a, reinscripcion r, grado g, secciones s
	WHERE a.id_alumno = r.id_alumno
		AND r.id_grado = g.id_grado
		AND r.id_seccion = s.id_seccion
	ORDER BY a.id_alumno DESC' ;
$rowset = $db->sql_rowset($sql);

?>

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
			{ field: "nombre_alumno", title: 'Nombres', encoded: false },
			{ field: "apellido", title: 'Apellidos', encoded: false },
			{ field: "nombre", title: 'Grado', width: 100 },
			{ field: "nombre_seccion", title: 'Seccion', width: 80 },
			{ field: "fecha", title: "Fecha", width: 125 },
			{ title: "Compromiso", template: t_compromiso, width: 125 },
			{ title: 'Editar', template: t_editar, width: 80 }
		]
	});
});

function t_compromiso(a) {
	var r = '<a href="/reportes/compromiso.php?id_alumno=' + a.id_alumno + '" target="_blank"><img src="/public/images/iconos/39.ico" width="20" /></a>';

	return r;
}

function t_editar(a) {
	return '<a href="/mantenimientos/alumnos/alumno.php?carne=' + a.carne + '&amp;Submit2=Buscar" target="_blank"><img src="/public/images/iconos/226.ico" width="20" /></a>';
}
</script>

<?php pie(); ?>