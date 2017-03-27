<?php

//
// TODO: Dynamic timezone
//
function get_datetime($select) {
    return date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $select) . ' +6 hours'));
}

function get_now() {
    return date('Y-m-d H:i:s');
}

//
// Get assigned courses for student and current teacher
//
function get_assigned_courses($student_id, $teacher = false) {
    global $user;

    if ($teacher === false) {
        $teacher = $user->d('user_id');
    }

    if ($user->is('founder')) {
        $sql = 'SELECT c.id_curso, c.nombre_curso
            FROM cursos c
            INNER JOIN reinscripcion r ON r.id_seccion = c.id_section
            WHERE r.id_alumno = ?';
        $sql = sql_filter($sql, $student_id);
    } else {
        $sql = 'SELECT c.id_curso, c.nombre_curso
            FROM cursos c
            INNER JOIN reinscripcion r ON r.id_seccion = c.id_section
            INNER JOIN catedratico a ON a.id_member = c.id_catedratico
            WHERE r.id_alumno = ?
                AND a.id_member = ?';
        $sql = sql_filter($sql, $student_id, $teacher);
    }

    $courses = sql_rowset($sql);

    return $courses;
}

function get_all_teachers() {
    $sql = 'SELECT *
        FROM catedratico
        ORDER BY nombre_catedratico, apellido';
    return sql_rowset($sql);
}

function get_teacher_by_id($teacher) {
    $sql = 'SELECT *
        FROM catedratico
        WHERE id_catedratico = ?';
    return sql_fieldrow(sql_filter($sql, $teacher));
}

function get_teacher_courses($teacher = false) {
    global $user;

    if ($teacher === false) {
        $teacher = $user->d('user_id');
    }

    $sql = 'SELECT c.id_curso, c.nombre_curso
        FROM cursos c
        INNER JOIN catedratico a ON a.id_member = c.id_catedratico
        WHERE a.id_member = ?';
    $courses = sql_rowset(sql_filter($sql, $student_id, $teacher));

    return $courses;
}

function get_teacher_grade_section($teacher = false) {
    global $user;

    if ($teacher === false) {
        $teacher = $user->d('user_id');
    }

    $sql = 'SELECT DISTINCT g.id_grado, g.nombre, s.id_seccion, s.nombre_seccion
        FROM catedratico c
        INNER JOIN cursos u ON u.id_catedratico = c.id_member
        INNER JOIN grado g ON g.id_grado = u.id_grado
        INNER JOIN secciones s ON g.id_grado = s.id_grado
        WHERE c.id_member = ?
        ORDER BY g.id_grado';
    return sql_rowset(sql_filter($sql, $teacher));
}

function get_assigned_grade_courses($section, $teacher = false) {
    global $user;

    if ($teacher === false) {
        $teacher = $user->d('user_id');
    }

    if ($user->is('founder')) {
        $sql = 'SELECT u.id_curso, u.nombre_curso
            FROM cursos u
            WHERE u.id_section = ?
            ORDER BY u.nombre_curso';
        $list = sql_rowset(sql_filter($sql, $section));
    } else {
        $sql = 'SELECT u.id_curso, u.nombre_curso
            FROM cursos u
            INNER JOIN catedratico c ON u.id_catedratico = c.id_member
            WHERE c.id_member = ?
                AND u.id_section = ?
            ORDER BY u.nombre_curso';
        $list = sql_rowset(sql_filter($sql, $teacher, $section));
    }

    return $list;
}

function get_grades_sections() {
    $sql = 'SELECT *
        FROM grado g, secciones s
        WHERE g.id_grado = s.id_grado
            AND g.status = ?';
    return sql_rowset(sql_filter($sql, 'Alta'));
}

function get_grade_section($grade, $section) {
    $sql = 'SELECT *
        FROM grado g, secciones s
        WHERE g.id_grado = s.id_grado
            AND g.id_grado = ?
            AND s.id_seccion = ?';
    return sql_fieldrow(sql_filter($sql, $grade, $section));
}

function get_students_grade_section($grade, $section, $year = false) {
    if ($year === false) {
        $year = date('Y');
    }

    $sql = 'SELECT *
        FROM alumno a, grado g, reinscripcion r
        WHERE r.id_alumno = a.id_alumno
            AND g.id_grado = r.id_grado
            AND r.id_grado = ?
            AND r.id_seccion = ?
            AND r.anio = ?
        ORDER BY a.apellido, a.nombre_alumno ASC';
    return sql_rowset(sql_filter($sql, $grade, $section, $year));
}

function get_grade($grade = 1) {
    $sql = 'SELECT *
        FROM grado
        WHERE id_grado = ?';
    return sql_fieldrow(sql_filter($sql, $grade));
}

function get_grades($status = 'Alta') {
    $sql = 'SELECT *
        FROM grado
        WHERE status = ?
        ORDER BY grade_order';
    return sql_rowset(sql_filter($sql, $status));
}

function get_section($section = 1) {
    $sql = 'SELECT id_grado, nombre_seccion
        FROM secciones
        WHERE id_seccion = ?';
    return sql_fieldrow(sql_filter($sql, $section));
}

function get_sections($section = 1) {
    $sql = 'SELECT *
        FROM secciones
        WHERE id_grado = ?';
    return sql_rowset(sql_filter($sql, $section));
}

function get_grade_courses($grade = 1) {
    $sql = 'SELECT *
        FROM cursos
        WHERE id_grado = ?';
    return sql_rowset(sql_filter($sql, $grade));
}

function get_course($course = 1) {
    $sql = 'SELECT *
        FROM cursos
        WHERE id_curso = ?';
    return sql_fieldrow(sql_filter($sql, $course));
}

function get_all_exams() {
    $sql = 'SELECT *
        FROM examenes';
    return sql_rowset($sql);
}

function get_exam_group($exam = 1) {
    $sql = 'SELECT *
        FROM examenes
        WHERE id_examen = ?';
    return sql_fieldrow(sql_filter($sql, $exam));
}

function search_wildcard_students($firstname, $lastname) {
    $sql = 'SELECT id_alumno, carne, apellido, nombre_alumno
        FROM alumno a
        INNER JOIN _members m ON m.user_id = a.id_member
        WHERE m.username_base LIKE ?
            AND m.username_base LIKE ?
        ORDER BY a.apellido, a.nombre_alumno';
    return sql_rowset(sql_filter($sql, '%' . $firstname . '%', '%' . $lastname . '%'));
}

function search_student_text($str) {
    $sql = 'SELECT m.user_id as id, m.username as text
        FROM alumno a
        INNER JOIN _members m ON a.id_member = m.user_id
        WHERE m.username LIKE ?
        ORDER BY m.username';
    return sql_rowset(sql_filter($sql, '%' . $str . '%'));
}

function all_students() {
    $sql = 'SELECT a.id_alumno, a.carne, a.fecha, a.nombre_alumno, a.apellido, g.nombre, s.nombre_seccion
        FROM alumno a, reinscripcion r, grado g, secciones s
        WHERE a.id_alumno = r.id_alumno
            AND r.id_grado = g.id_grado
            AND r.id_seccion = s.id_seccion
        ORDER BY a.id_alumno ASC';
    return sql_rowset($sql);
}

function get_student_by_id($student, $columns = false, $field = false) {
    $columns = $columns ?: '*';
    $field = $field ?: 'id_alumno';

    $sql = 'SELECT ??
        FROM alumno
        WHERE ?? = ?';
    return sql_fieldrow(sql_filter($sql, $columns, $field, $student));
}

function get_student_info($carne, $columns = false, $all_years = false) {
    $columns = $columns ?: 'a.id_alumno, a.carne, a.nombre_alumno, a.apellido, g.nombre AS nombre_grado, s.nombre_seccion';

    $sql = 'SELECT ??
        FROM alumno a
        INNER JOIN reinscripcion r ON r.id_alumno = a.id_alumno
        INNER JOIN grado g ON g.id_grado = r.id_grado
        INNER JOIN secciones s ON s.id_seccion = r.id_seccion
        WHERE a.carne = ?';
    $sql = sql_filter($sql, $columns, $carne);

    if ($all_years !== false) {
        $sql . ' ORDER BY r.anio DESC';
        $list = sql_rowset($sql);
    } else {
        $list = sql_fieldrow($sql);
    }

    return $list;
}

function get_student_own_tasks($student = false) {
    global $user;

    if ($student === false) {
        $student = $user->d('user_id');
    }

    $sql = 'SELECT *, c.apellido as apellido_catedratico
        FROM alumno a, reinscripcion r, _activities ac, _activities_assigned aa,
            catedratico c, grado g, secciones s, areas_cursos acu, cursos cu
        WHERE a.id_member = ?
            AND aa.assigned_student = a.id_member
            AND a.id_alumno = r.id_alumno
            AND aa.assigned_activity = ac.activity_id
            AND c.id_member = ac.activity_teacher
            AND s.id_grado = g.id_grado
            AND s.id_seccion = ac.activity_group
            AND acu.id_area = cu.id_area
            AND ac.activity_schedule = cu.id_curso';
    return sql_rowset(sql_filter($sql, $student));
}

function get_teacher_own_tasks($teacher = false) {
    global $user;

    if ($teacher === false) {
        $teacher = $user->d('user_id');
    }

    $sql = 'SELECT *
        FROM _activities a
        INNER JOIN _activities_assigned d ON d.assigned_activity = a.activity_id
        INNER JOIN cursos u ON a.activity_schedule = u.id_curso
        INNER JOIN secciones s ON a.activity_group = s.id_seccion
        INNER JOIN grado g ON s.id_grado = g.id_grado
        WHERE a.activity_teacher = ?
        GROUP BY d.assigned_activity
        ORDER BY a.created_at DESC';

    return sql_rowset(sql_filter($sql, $teacher));
}

function create_student_activity($task_id, $student_id) {
    $now = get_now();

    $sql_insert = array(
        'assigned_activity'  => $task_id,
        'assigned_student'   => $student_id,
        'assigned_delivered' => 0,
        'assigned_total'     => 0,
        'assigned_comments'  => 1,
        'created_at'         => $now,
        'updated_at'         => $now
    );
    return sql_insert('activities_assigned', $sql_insert);
}

function create_student_code($id = '', $gender = false, $year = false) {
    $gender ?: 'M';
    $year   ?: date('Y');

    return $year . $gender . $id;
}

function get_user_id($username) {
    $base = simple_alias($username);

    $sql = 'SELECT user_id
        FROM _members
        WHERE username_base = ?';
    return sql_field(sql_filter($sql, $base), 'user_id', 0);
}

function update_student_info($student_id, $student_ary) {
    $sql = 'UPDATE alumno SET ' . sql_build('UPDATE', $student_ary) . sql_filter('
        WHERE id_alumno = ?', $student_id);
    return sql_query($sql);
}

function choose_gender($value) {
    $gender = array(
        'M' => 1,
        'F' => 2
    );
    return isset($gender[$value]) ? $gender[$value] : 1;
}

function create_student_info($ary) {
    $default = array(
        'carne'            => 0,
        'codigo_alumno'    => '',
        'nombre_alumno'    => '',
        'apellido'         => '',
        'direccion'        => '',
        'orden'            => '',
        'registro'         => '',
        'telefono1'        => '',
        'edad'             => '',
        'sexo'             => '',
        'email'            => '',
        'padre'            => '',
        'madre'            => '',
        'encargado'        => '',
        'profesion'        => '',
        'labora'           => '',
        'direccion_labora' => '',
        'email_encargado'  => '',
        'dpi'              => '',
        'extendida'        => '',
        'emergencia'       => '',
        'telefono2'        => '',
        'status'           => 'Inscrito'
    );
    $ary = array_merge($default, $ary);

    $ary['edad'] = sprintf("%02d", $ary['edad']);

    $user_id = sql_create('alumno', $ary);

    $carne = create_student_code($user_id, $ary['sexo']);

    $student_update = array(
        'carne'     => $carne
    );
    update_student_info($user_id, $student_update);

    return [
        'student_id'    => $user_id,
        'student_carne' => $carne
    ];
}

function create_current_student($ary) {
    $default = array(
        'id_alumno'               => 0,
        'carne'                   => '',
        'id_grado'                => 0,
        'id_seccion'              => 0,
        'encargado_reinscripcion' => 0,
        'telefonos'               => 0,
        'status'                  => 'Inscrito',
        'anio'                    => date('Y')
    );
    $ary = array_merge($default, $ary);

    $create_id = sql_create('reinscripcion', $ary);

    return $create_id;
}

function create_student_supervisor($supervisor, $student) {
    $insert = array(
        'supervisor' => $supervisor,
        'student'    => $student
    );
    return sql_create('alumnos_encargados', $insert);
}

function get_recent_students($limit = 25) {
    $year = date('Y');

    $sql = 'SELECT a.id_alumno, a.carne, a.fecha, a.nombre_alumno, a.apellido, g.nombre, s.nombre_seccion
        FROM alumno a, reinscripcion r, grado g, secciones s
        WHERE a.id_alumno = r.id_alumno
            AND r.id_grado = g.id_grado
            AND r.id_seccion = s.id_seccion
            AND r.anio = ?
        ORDER BY a.id_alumno DESC
        LIMIT ?';
    return sql_rowset(sql_filter($sql, $year, $limit));
}

function get_daily_student_attends($schedule, $section, $day, $teacher = false) {
    global $user;

    if ($teacher === false) {
        $teacher = $user->d('user_id');
    }

    $sql = 'SELECT *
        FROM _student_attends
        WHERE attend_schedule = ?
            AND attend_teacher = ?
            AND attend_group = ?
            AND attend_date = ?';
    return sql_rowset(sql_filter($sql, $schedule, $teacher, $section, $day));
}

function get_students_for_tasks($select, $section = false, $year = false) {
    if ($select !== false) {
        $sql = 'SELECT user_id, username
            FROM _members
            WHERE username IN (' . implode(', ', array_fill(0, count($select), '?')) . ')
            ORDER BY user_id';
        $list = sql_rowset(sql_filter($sql, $select), 'user_id', 'username');
    } else {
        $year = $year ?: date('Y');

        $sql = 'SELECT m.user_id, m.username
            FROM _members m
            INNER JOIN alumno a ON m.user_id = a.id_member
            INNER JOIN reinscripcion r ON r.id_alumno = a.id_alumno
            WHERE r.id_seccion = ?
                AND r.anio = ?
            ORDER BY m.username';
        $list = sql_rowset(sql_filter($sql, $section, $year));
    }

    return $list;
}

function create_teacher_activity($ary) {
    global $user;

    $now = get_now();

    $default = array(
        'activity_name'        => '',
        'activity_description' => '',
        'activity_start'       => date('Y-m-d'),
        'activity_end'         => date('Y-m-d'),
        'activity_show'        => 1,
        'activity_teacher'     => $user->d('user_id'),
        'activity_schedule'    => '',
        'activity_group'       => '',
        'activity_ip'          => $user->ip,
        'created_at'           => $now,
        'updated_at'           => $now,
    );
    $ary = array_merge($default, $ary);

    $ary['activity_start'] = get_datetime($ary['activity_start']);
    $ary['activity_end']   = get_datetime($ary['activity_start']);

    return sql_insert('activities', $ary);
}

function get_area_courses() {
    $sql = 'SELECT *
        FROM areas_cursos
        ORDER BY rel_order';
    return sql_rowset($sql);
}

function get_course_grades_teachers() {
    $sql = 'SELECT *
        FROM cursos u
        INNER JOIN grado g ON u.id_grado = g.id_grado
        INNER JOIN secciones s ON g.id_grado = s.id_grado
        INNER JOIN catedratico c ON c.id_member = u.id_catedratico
        WHERE g.status = ?
        ORDER BY u.id_grado';
    return sql_rowset(sql_filter($sql, 'Alta'));
}

function get_student_single_score($student, $grade, $course, $exam) {
    $sql = 'SELECT id_nota, nota
        FROM notas
        WHERE id_alumno = ?
            AND id_grado = ?
            AND id_curso = ?
            AND id_bimestre = ?';
    return sql_fieldrow(sql_filter($sql, $student, $grade, $course, $exam));
}

function do_score_remove($score_id) {
    $sql = 'DELETE FROM notas
        WHERE id_nota = ?';
    return sql_query(sql_filter($sql, $score_id));
}

function do_score_update($score_id, $score_value) {
    $sql = 'UPDATE notas SET nota = ?
        WHERE id_nota = ?';
    return sql_query(sql_filter($sql, $score_value, $score_id));
}

function get_faults_list($limit = 50) {
    $sql = 'SELECT DISTINCT *
        FROM alumno a, faltas f
        WHERE a.id_alumno = f.id_alumno
        GROUP BY a.carne
        ORDER BY a.apellido, a.nombre_alumno DESC
        LIMIT ??';
    return sql_rowset(sql_filter($sql, $limit));
}

function get_student_faults($student, $order = 'DESC') {
    $sql = 'SELECT *
        FROM faltas f
        INNER JOIN cursos c ON c.id_curso = f.course_id
        LEFT JOIN catedratico a ON a.id_member = f.teacher_id
        WHERE f.id_alumno = ?
        ORDER BY f.id_falta ??';
    return sql_rowset(sql_filter($sql, $student, $order));
}
