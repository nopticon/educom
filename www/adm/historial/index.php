<?php

require_once('../conexion.php');

if (request_var('submit', '')) {
    $carne = request_var('carne', '');

    if (!$alumno = get_student_by_id($carne, 'carne, nombre_alumno, apellido, madre, padre', 'carne')) {
        location('.');
    }

    $list = get_student_info($carne, 'r.anio, g.nombre, s.nombre_seccion, r.encargado_reinscripcion, r.id_alumno, s.id_grado, s.id_seccion');

    if (!is_array($list)) {
        $list = [$list];
    }

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
