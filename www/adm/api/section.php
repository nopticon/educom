<?php

require_once('../conexion.php');

$grado = request_var('grado', 0);

if (!$grado) {
    exit;
}

$format = '<option value="%s">%s</option>';
$result = get_sections($grado);

foreach ($result as $row) {
    echo sprintf($format, $row->id_seccion, $row->nombre_seccion);
}

exit;
