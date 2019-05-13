<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../www/adm/conexion.php';

$sql = 'SELECT m.username, m.user_upw, g.nombre, s.nombre_seccion
    FROM _members m, alumno a, reinscripcion r, grado g, secciones s
    WHERE m.user_id > 2
        AND m.user_id = a.id_member
        AND a.id_alumno = r.id_alumno
        AND r.id_grado = g.id_grado
        AND r.id_seccion = s.id_seccion
    ORDER BY user_id';
$rowset = sql_rowset($sql);

echo build_table($rowset);

$sql = 'SELECT m.username, m.user_upw, g.nombre, s.nombre_seccion
    FROM _members m, _members m2, alumno a, reinscripcion r, grado g, secciones s, alumnos_encargados ae
    WHERE m2.user_id = a.id_member
        AND a.id_alumno = r.id_alumno
        AND r.id_grado = g.id_grado
        AND r.id_seccion = s.id_seccion
        AND ae.supervisor = m.user_id
        AND ae.student = m2.user_id';
$rowset = sql_rowset($sql);

echo build_table($rowset);

$sql = 'SELECT m.username, m.user_upw
    FROM _members m, catedratico c
    WHERE m.user_id = c.id_member
    ORDER BY m.user_id';
$rowset = sql_rowset($sql);

echo build_table($rowset);
