<?php

require_once('../../conexion.php');

$dateselect = request_var('dateselect', '');
$schedule   = request_var('schedule', 0);
$section    = request_var('section', 0);
$marked     = request_var('marked', array(0 => 0));

$ary_date = explode('/', $dateselect);
$anio     = $ary_date[2];

$teacher         = $user->d('user_id');
$calculated_date = date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $dateselect) . ' +6 hours'));

$sql = 'SELECT a.id_alumno, a.id_member, a.nombre_alumno
    FROM alumno a, grado g, reinscripcion r
    WHERE r.id_alumno = a.id_alumno
        AND g.id_grado = r.id_grado
        AND r.id_seccion = ?
        AND r.anio = ?
    ORDER BY a.apellido, a.nombre_alumno ASC';
$list = $db->sql_rowset(sql_filter($sql, $section, $anio));

$sql = 'SELECT *
    FROM _student_attends
    WHERE attend_schedule = ?
        AND attend_teacher = ?
        AND attend_group = ?
        AND attend_date = ?';
$existing = sql_rowset(sql_filter($sql, $schedule, $teacher, $section, $calculated_date));

foreach ($list as $row) {
    if (!isset($marked[$row->id_alumno])) {
        $marked[$row->id_alumno] = 0;
    }

    $sql_insert = array(
        'attend_member'   => $row->id_member,
        'attend_schedule' => $schedule,
        'attend_teacher'  => $teacher,
        'attend_group'    => $section,
        'attend_date'     => $calculated_date,
        'attend_value'    => $marked[$row->id_alumno]
    );
    $attend_id = sql_insert('student_attends', $sql_insert);
}

$_SESSION['attend_message'] = 'La asistencia fue creada correctamente.';

location('listado_alumno.php');
