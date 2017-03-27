<?php

require_once('../conexion.php');

$student = request_var('q', '');

if (!$student) {
    exit;
}

$result = search_student_text($student);

$response = [];
foreach ($result as $row) {
    $response[] = $row;
}

json_header();

echo json_encode($response);
exit;
