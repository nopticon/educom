<?php

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

function get_grade_section($grade, $section) {
    $sql = 'SELECT *
        FROM grado g, secciones s
        WHERE g.id_grado = ?
            AND s.id_seccion = ?
            AND g.id_grado = s.id_grado';
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

function get_grades($status = 'Alta') {
    $sql = 'SELECT *
        FROM grado
        WHERE status = ?';
    return sql_rowset(sql_filter($sql, $status));
}

function get_sections($section = 1) {
    $sql = 'SELECT *
        FROM secciones
        WHERE id_grado = ?';
    return sql_rowset(sql_filter($sql, $section));
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
