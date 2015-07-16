<?php

require_once('../conexion.php');

$id_alumno = request_var('id_alumno', 0);
$teacher_id = request_var('teacher_id', 0);
$fault_date = request_var('fault_date', '');
$course_id = request_var('course_id', 0);
$tipo_falta = request_var('tipo_falta', '');
$fault_description = request_var('fault_description', '');

if (empty($fault_description)) {
	location('.');
}

$sql = 'SELECT *
	FROM alumno
	WHERE id_alumno = ?';
if (!$student = sql_fieldrow(sql_filter($sql, $id_alumno))) {
	location('.');
}

$sql_insert = array(
	'id_alumno' => $id_alumno,
	'course_id' => $course_id,
	'teacher_id' => $teacher_id,
	'tipo_falta' => $tipo_falta,
	'falta' => $fault_description,
	'fecha_falta' => date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $fault_date) . ' +6 hours'))
);
$sql = 'INSERT INTO faltas' . $db->sql_build('INSERT', $sql_insert);
$db->sql_query($sql);

$_SESSION['guardar'] = 1;

location('faltas2.php?carne1=' . $student->carne . '&submit=Continuar');