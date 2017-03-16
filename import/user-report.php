<?php

require 'vendor/autoload.php';

require_once('../adm/conexion.php');

$sql = 'SELECT *
	FROM _members m, alumno a, reinscripcion r, grado g, secciones s
	WHERE m.user_id > 2
		AND m.user_id = a.id_member
		AND a.id_alumno = r.id_alumno
		AND r.id_grado = g.id_grado
		AND r.id_seccion = s.id_seccion
	ORDER BY user_id';
$rowset = $db->sql_rowset($sql);

echo '<table border="1">';

foreach ($rowset as $row) {
	echo '<tr><td>' . $row->username . '</td><td>' . $row->user_upw . '</td><td>' . $row->nombre . '</td><td>' . $row->nombre_seccion . '</td></tr>';
}

echo '</table>';

echo '<br /><br />';

$sql = 'SELECT m.*, g.*, s.*
	FROM _members m, _members m2, alumno a, reinscripcion r, grado g, secciones s, alumnos_encargados ae
	WHERE m.user_id > 2
		AND m2.user_id = a.id_member
		AND a.id_alumno = r.id_alumno
		AND r.id_grado = g.id_grado
		AND r.id_seccion = s.id_seccion
		AND ae.supervisor = m.user_id
		AND ae.student = m2.user_id
	ORDER BY user_id';
$rowset = $db->sql_rowset($sql);

echo '<table border="1">';

foreach ($rowset as $row) {
	echo '<tr><td>' . $row->username . '</td><td>' . $row->user_upw . '</td><td>' . $row->nombre . '</td><td>' . $row->nombre_seccion . '</td></tr>';
}

echo '</table>';

echo '<br /><br />';

$sql = 'SELECT *
	FROM _members m, catedratico c
	WHERE m.user_id = c.id_member
	ORDER BY m.user_id';
$rowset = $db->sql_rowset($sql);

echo '<table border="1">';

foreach ($rowset as $row) {
	echo '<tr><td>' . $row->username . '</td><td>' . $row->user_upw . '</td></tr>';
}

echo '</table>';
