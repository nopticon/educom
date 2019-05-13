<?php

require_once('../conexion.php');

$group = request_var('group', 0);

if (!$group || !$user->is('member')) {
    exit;
}

json_header();

echo json_encode(get_assigned_grade_courses($group));
exit;
