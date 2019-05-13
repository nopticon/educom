<?php

require_once('../../conexion.php');

$id_grado = request_var('id_grado', 0);
$nombre   = request_var('grado', '');
$status   = request_var('status', '');

if (empty($nombre)) {
    location('../../grados/');
}

$sql_update = array(
    'nombre' => $nombre,
    'status' => $status,
);
$sql = 'UPDATE grado SET' . $db->sql_build('UPDATE', $sql_update) . sql_filter('
    WHERE id_grado = ?', $id_grado);
$db->sql_query($sql);

location('../../grados/');
