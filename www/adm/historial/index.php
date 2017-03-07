<?php

require_once('../conexion.php');

if (request_var('submit', '')) {
    $carne = request_var('carne', '');

    $sql = 'SELECT *
        FROM alumno
        WHERE carne = ?';
    if (!$alumno = $db->sql_fieldrow(sql_filter($sql, $carne))) {
        location('.');
    }

    $sql = 'SELECT *
        FROM reinscripcion r, alumno a, grado g, secciones s
        WHERE r.id_alumno = a.id_alumno
            AND r.id_grado = g.id_grado
            AND s.id_seccion = r.id_seccion
            AND s.id_grado = r.id_grado
            AND r.carne = ?
        ORDER BY r.anio DESC';
    $list = $db->sql_rowset(sql_filter($sql, $carne));

    foreach ($list as $i => $row) {
        if (!$i) {
            _style('results', $alumno);
        }

        _style('results.row', $row);
    }
} else {
    $form = [[
        'carne' => [
            'type'  => 'text',
            'value' => 'Carn&eacute;'
        ]
    ]];

    _style('create', [
        'form'   => build_form($form),
        'submit' => build_submit()
    ]);
}

page_layout('Historial del Alumno', 'student_history');
