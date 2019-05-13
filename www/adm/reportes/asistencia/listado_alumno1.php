<?php

require_once('../../conexion.php');

encabezado('Asistencia de alumnos', '', false);

$grado      = request_var('grado', 0);
$seccion    = request_var('seccion', 0);
$dateselect = request_var('dateselect', '');

$ary_date = explode('/', $dateselect);
$anio     = $ary_date[2];
$teacher  = $user->d('user_id');

$grado_seccion    = get_grade_section($grado, $seccion);
$list             = get_students_grade_section($grado, $seccion, $anio);
$assigned_courses = get_assigned_grade_courses($seccion, $teacher);
// $existing         = get_daily_student_attends(315, $seccion, get_datetime($dateselect));

?>

<h2>Grado: <?php echo $grado_seccion->nombre . ' ' . $grado_seccion->nombre_seccion; ?></h2>
<h2>Fecha: <strong><?php echo $dateselect; ?></strong></h2>

<form action="listado_alumno2.php" method="post" id="form_students">
    <input type="hidden" name="dateselect" value="<?php echo $dateselect; ?>" />
    <input type="hidden" name="section" value="<?php echo $seccion; ?>" />

    <br />
    <table class="table table-bordered">
        <tr>
            <td>Asignatura:</td>
            <td>
                <select name="schedule">
                    <?php

                    foreach ($assigned_courses as $row) {
                        echo '<option value="' . $row->id_curso . '">' . $row->nombre_curso . '</option>';
                    }

                    ?>
                </select>
            </td>
        </tr>
        <tr>
            <td>Catedr&aacute;tico:</td>
            <td><?php echo $user->d('username'); ?></td>
        </tr>
    </table>

    <table class="table table-striped">
        <thead>
            <tr>
                <td>#</td>
                <td>Carn&eacute;</td>
                <td>Apellido</td>
                <td>Nombre</td>
                <td>Marcar si asisti&oacute;</td>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($list as $i => $row) { ?>
            <tr>
                <th scope="row"><?php echo ($i + 1); ?></th>
                <td><?php echo $row->carne; ?></td>
                <td><?php echo $row->apellido; ?></td>
                <td><?php echo $row->nombre_alumno; ?></td>
                <td><input type="checkbox" name="marked[<?php echo $row->carne; ?>]" value="1" /></td>
            </tr>
            <?php } ?>
        </tbody>
    </table>

    <?php submit('Guardar asistencia'); ?>
</form>

<script type="text/javascript">
$(function() {
    var checkboxes = $('#form_students').find(':checkbox');
    checkboxes.prop('checked', true);
});
</script>

<?php pie();
