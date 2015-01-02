<?php

require_once("./conexion.php");

$grado = $_REQUEST['grado'];

$sql = 'SELECT *
	FROM secciones
	WHERE id_grado = ?';
$result = $db->sql_rowset($db->__prepare($sql, $grado));

foreach ($result as $row) {
	echo '<option value="' . $row->id_seccion . '">' . $row->nombre_seccion . '</option>';
}

exit;