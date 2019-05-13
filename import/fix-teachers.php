<?php

require __DIR__.'/../vendor/autoload.php';
require __DIR__.'/../www/adm/conexion.php';

$sql = 'SELECT m.user_id, m.username, m.user_upw
    FROM _members m, catedratico c
    WHERE m.user_id = c.id_member
    ORDER BY m.user_id';
$rowset = sql_rowset($sql);

$teachers = array();

foreach ($rowset as $row) {
    $alias = simple_alias($row->username);
    $teachers[$alias][] = $row;
}

foreach ($teachers as $alias => $row) {
    if (count($row) < 2) continue;

    $first = array_shift($row);

    foreach ($row as $row_teacher) {
        $sql = 'SELECT *
            FROM catedratico
            WHERE id_member = ?';
        $sql_row = sql_fieldrow(sql_filter($sql, $row_teacher->user_id));

        $sql = 'DELETE FROM catedratico WHERE id_catedratico = ' . $sql_row->id_catedratico;
        sql_query($sql);

        $sql = 'UPDATE cursos SET id_catedratico = ? WHERE id_catedratico = ?';
        sql_query(sql_filter($sql, $row_teacher->user_id, $sql_row->id_catedratico));

        $sql = 'DELETE FROM _members WHERE user_id = ?';
        sql_query(sql_filter($sql, $row_teacher->user_id));
    }
}

$sql = 'SELECT m.user_id, m.username, m.user_upw, c.id_catedratico
    FROM _members m, catedratico c
    WHERE m.user_id = c.id_member
    ORDER BY m.user_id';
$rowset = sql_rowset($sql);

$updated = array();
foreach ($rowset as $row) {
    $sql = 'UPDATE cursos SET id_catedratico = ? WHERE id_catedratico = ?';
    $sql = sql_filter($sql, $row->id_catedratico, $row->user_id);

    sql_query($sql);

    $updated[] = $sql;
}

dd($updated);
