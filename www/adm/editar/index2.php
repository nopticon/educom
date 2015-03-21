<?php

require_once("../conexion.php");

$grado = request_var('grado', 0);

$sql = 'SELECT id_grado
	FROM secciones
	WHERE id_seccion = ?';
if (!$secciones = $db->sql_rowset(sql_filter($sql, $grado))) {
	exit;
}

$sql = 'SELECT *
	FROM cursos
	WHERE id_grado = ?';
$cursos = $db->sql_rowset(sql_filter($sql, $secciones['id_grado']));

foreach ($cursos as $row)
	echo '<option value="' . $row->id_curso . '">' . $row->nombre_curso . '</option>';
}

exit;