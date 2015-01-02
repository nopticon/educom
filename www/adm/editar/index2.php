<?php

require_once("../conexion.php");

if (!isset($_REQUEST['grado'])) {
	header('Location: index.php');
	exit;
}

$grado = $_REQUEST['grado'];

$sql = 'SELECT id_grado
	FROM secciones
	WHERE id_seccion = ?';
if (!$secciones = $db->sql_rowset($db->__prepare($sql, $grado))) {
	exit;
}

$sql = 'SELECT *
	FROM cursos
	WHERE id_grado = ?';
$cursos = $db->sql_rowset($db->__prepare($sql, $gradoar['id_grado']));

foreach ($cursos as $row)
	echo '<option value="' . $row->id_curso . '">' . $row->nombre_curso . '</option>';
}

exit;