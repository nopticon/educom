<?php

require_once('../conexion.php');

$group = request_var('group', 0);

if (!$group || !$user->is('member')) {
	exit;
}

$sql = 'SELECT u.id_curso, u.nombre_curso
	FROM catedratico c
	INNER JOIN cursos u ON u.id_catedratico = c.id_catedratico
	WHERE c.id_member = ?
		AND u.id_section = ?
	ORDER BY u.nombre_curso';
$assigned_courses = sql_rowset(sql_filter($sql, $user->d('user_id'), $group));

header('Content-Type: application/json');

echo json_encode($assigned_courses);
exit;