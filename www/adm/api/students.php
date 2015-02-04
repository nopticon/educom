<?php

require_once('../conexion.php');

header('Content-Type: application/json');

$student = request_var('q', '');

if (!$student) {
	exit;
}

$sql = 'SELECT m.user_id, m.username
	FROM alumno a
	INNER JOIN _members m ON a.id_member = m.user_id
	WHERE m.username LIKE ?
	ORDER BY m.username';
// echo sql_filter($sql, '%' . $student . '%');exit;
$result = sql_rowset(sql_filter($sql, '%' . $student . '%'), false, 'username');

echo json_encode($result);
exit;