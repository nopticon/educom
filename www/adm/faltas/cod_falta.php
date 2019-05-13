<?php

require_once('../conexion.php');

$id_alumno         = request_var('id_alumno', 0);
$teacher_id        = request_var('teacher_id', 0);
$fault_date        = request_var('fault_date', '');
$course_id         = request_var('course_id', 0);
$tipo_falta        = request_var('tipo_falta', '');
$fault_description = request_var('fault_description', '');

if (empty($fault_description)) {
    location('.');
}

if (!$student = get_student_by_id($id_alumno, 'carne')) {
    location('.');
}

$sql_insert = array(
    'id_alumno'   => $id_alumno,
    'course_id'   => $course_id,
    'teacher_id'  => $teacher_id,
    'tipo_falta'  => $tipo_falta,
    'falta'       => $fault_description,
    'fecha_falta' => get_datetime($fault_date)
);
sql_create('faltas', $sql_insert);

$_SESSION['guardar'] = 1;

location('faltas2.php?carne=' . $student->carne . '&submit=Continuar');
