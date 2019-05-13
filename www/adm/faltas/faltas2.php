<?php

require_once('../conexion.php');

$carne = request_var('carne', '');

if (!$alumno = get_student_info($carne)) {
    location('.');
}

$list = get_student_faults($alumno->id_alumno);

encabezado('Historial de Faltas ' . date('Y'));

?>

<table class="table table-bordered">
    <tbody>
        <tr>
            <td>Carn&eacute;</td>
            <td><?php echo $alumno->carne; ?></td>
        </tr>
        <tr>
            <td>Nombre</td>
            <td><?php echo $alumno->nombre_alumno; ?></td>
        </tr>
        <?php if ($alumno->apellido) { ?>
        <tr>
            <td>Apellido</td>
            <td><?php echo $alumno->apellido; ?></td>
        </tr>
        <?php } ?>
        <tr>
              <td>Grado</td>
            <td><?php echo $alumno->nombre_grado . ' ' . $alumno->nombre_seccion; ?></td>
        </tr>
    </tbody>
</table>

<table class="table table-striped">
    <thead>
        <td width="20%">Fecha de Ingreso</td>
        <td width="10%">Tipo</td>
        <td>Descripci&oacute;n</td>
        <td>Curso</td>
        <td>Catedr&aacute;tico</td>
    </thead>

    <?php foreach ($list as $row) { ?>
    <tr>
        <td><?php echo $row->fecha_falta; ?></td>
        <td><?php echo $row->tipo_falta; ?></td>
        <td><?php echo $row->falta; ?></td>
        <td><?php echo $row->nombre_curso; ?></td>
        <td><?php echo $row->nombre_catedratico; ?></td>
    </tr>
    <?php } ?>
</table>

<?php pie(); ?>
