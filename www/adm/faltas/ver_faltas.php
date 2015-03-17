<?php

require_once('../conexion.php');

$id_falta = request_var('id_falta', 0);

if (!$id_falta) {
	redirect('/adm/faltas/');
}

$sql = 'SELECT *
	FROM alumno a, faltas f
	WHERE f.id_falta = ?
		AND a.id_alumno = f.id_alumno';
$list = $db->sql_rowset(sql_filter($sql, $id_falta));

redirect('/adm/faltas/faltas2.php?carne1=' . $list[0]->carne);