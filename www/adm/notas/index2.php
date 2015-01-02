<?php

require_once("../conexion.php");

$grado = $_REQUEST['grado'];

$sql = 'SELECT id_grado
	FROM secciones
	WHERE id_seccion = ?';
if (!$gradoar = $db->sql_fieldrow($db->__prepare($sql, $grado))) {
	redirect('index.php');
}

$sql = 'SELECT *
	FROM cursos
	WHERE id_grado = ?';
$list = $db->sql_rowset($db->__prepare($sql, $gradoar->id_grado));

foreach ($list as $row) {
	echo '<option value="' . $row->id_curso . '">' . $row->nombre_curso . '</option>';
}

exit;