<?php

require_once('../../conexion.php');

encabezado('Asistencia de alumnos', '', false);

$grado      = request_var('grado', 0);
$seccion    = request_var('seccion', 0);
$dateselect = request_var('dateselect', '');

$ary_date = explode('/', $dateselect);
$anio     = $ary_date[2];

$teacher         = $user->d('user_id');
$calculated_date = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $dateselect) . ' +6 hours'));
// $schedule = 315;

$sql = 'SELECT *
    FROM grado g, secciones s
    WHERE g.id_grado = ?
        AND s.id_seccion = ?
        AND g.id_grado = s.id_grado';
$grado_seccion = $db->sql_fieldrow(sql_filter($sql, $grado, $seccion));

$sql = 'SELECT *
    FROM alumno a, grado g, reinscripcion r
    WHERE r.id_alumno = a.id_alumno
        AND g.id_grado = r.id_grado
        AND r.id_grado = ?
        AND r.id_seccion = ?
        AND r.anio = ?
    ORDER BY a.apellido, a.nombre_alumno ASC';
$list = $db->sql_rowset(sql_filter($sql, $grado, $seccion, $anio));

$sql = 'SELECT u.id_curso, u.nombre_curso
    FROM catedratico c
    INNER JOIN cursos u ON u.id_catedratico = c.id_catedratico
    WHERE c.id_member = ?
        AND u.id_section = ?
    ORDER BY u.nombre_curso';
$assigned_courses = sql_rowset(sql_filter($sql, $teacher, $seccion));

// $sql = 'SELECT *
//     FROM _student_attends
//     WHERE attend_schedule = ?
//         AND attend_teacher = ?
//         AND attend_group = ?
//         AND attend_date = ?';
// $existing = sql_rowset(sql_filter($sql, $schedule, $teacher, $seccion, $calculated_date));

// _pre(sql_filter($sql, $schedule, $teacher, $seccion, $calculated_date));
// _pre($existing, true);

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

<?php pie(); ?>
