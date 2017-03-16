<?php

require_once('../conexion.php');

$grado = request_var('grado', 0);

if (!$grado) {
    exit;
}

$format = '<option value="%s">%s</option>';

$sql = 'SELECT *
    FROM secciones
    WHERE id_grado = ?';
$result = $db->sql_rowset(sql_filter($sql, $grado));

foreach ($result as $row) {
    echo sprintf($format, $row->id_seccion, $row->nombre_seccion);
}

exit;
