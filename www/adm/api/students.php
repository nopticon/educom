<?php

require_once('../conexion.php');

header('Content-Type: application/json');

$student = request_var('q', '');

if (!$student) {
    exit;
}

$sql = 'SELECT m.user_id as id, m.username as text
    FROM alumno a
    INNER JOIN _members m ON a.id_member = m.user_id
    WHERE m.username LIKE ?
    ORDER BY m.username';
$result = sql_rowset(sql_filter($sql, '%' . $student . '%'));

$response = [];
foreach ($result as $row) {
    $response[] = $row;
}

echo json_encode($response);
exit;
