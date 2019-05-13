<?php

require_once('../../conexion.php');

encabezado('Listado de alumnos por grado', '', false);

$grado   = request_var('grado', 0);
$seccion = request_var('seccion', 0);
$anio    = request_var('anio', 0);

$level = get_grade_section($grado, $seccion);
$list  = get_students_grade_section($grado, $seccion, $anio);

?>

<h2>Grado: <?php echo $level->nombre . ' ' . $level->nombre_seccion; ?></h2>

<br />
<table class="table table-bordered">
    <tr>
        <td>A&ntilde;o: <strong><?php echo $anio; ?></strong><br /></td>
    </tr>
</table>

<table class="table table-striped">
    <thead>
        <tr>
            <td>Carn&eacute;</td>
            <td>Apellido</td>
            <td>Nombre</td>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($list as $i => $row) { ?>
        <tr>
            <td><?php echo $row->carne; ?></td>
            <td><?php echo $row->apellido; ?></td>
            <td><?php echo $row->nombre_alumno; ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<?php pie();
