<?php

require_once('../conexion.php');

encabezado('Lista de Alumnos');

?>

<div id="list"></div>

<script type="text/javascript">
var target, grid;

<?php echo 'var kdata = ' . json_encode(all_students()) . ';'; ?>

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
            { field: "carne", title: "Carne", width: 100 },
            { field: "nombre_alumno", title: 'Nombres', encoded: false },
            { field: "apellido", title: 'Apellidos', encoded: false },
            { field: "nombre", title: 'Grado', encoded: false },
            { field: "nombre_seccion", title: 'Seccion', width: 80 },
            { title: 'Editar', template: t_editar, width: 80 }
        ]
    });
});

function t_compromiso(a) {
    var r = '<a href="/reportes/compromiso.php?id_alumno=' + a.id_alumno + '" target="_blank">Compromiso</a>';

    return r;
}

function t_editar(a) {
    return '<a href="/mantenimientos/alumnos/alumno.php?carne=' + a.carne + '&amp;Submit2=Buscar" target="_blank">Editar</a>';
}
</script>

<?php pie();
