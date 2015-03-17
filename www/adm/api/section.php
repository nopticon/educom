<?php

require_once('../conexion.php');

$grado = request_var('grado', 0);

if (!$grado) {
	exit;
}

$sql = 'SELECT *
	FROM secciones
	WHERE id_grado = ?';
$result = $db->sql_rowset(sql_filter($sql, $grado));

foreach ($result as $row) {
	echo '<option value="' . $row->id_seccion . '">' . $row->nombre_seccion . '</option>';
}

exit;