<?php

require_once("../conexion.php");

$grado = request_var('grado', 0);

$sql = 'SELECT id_grado
	FROM secciones
	WHERE id_seccion = ?';
if (!$gradoar = $db->sql_fieldrow(sql_filter($sql, $grado))) {
	location('.');
}

$sql = 'SELECT *
	FROM cursos
	WHERE id_grado = ?';
$list = $db->sql_rowset(sql_filter($sql, $gradoar->id_grado));

foreach ($list as $row) {
	echo '<option value="' . $row->id_curso . '">' . $row->nombre_curso . '</option>';
}

exit;