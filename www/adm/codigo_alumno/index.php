<?php

require_once('../conexion.php');

if (request_var('submit2', '')) {
    $list = request_var('textfield', array(0 => 0));

    foreach ($list as $alumno => $codigo) {
        if (empty($codigo)) {
            continue;
        }

        $sql_update = array(
            'codigo_alumno' => $codigo
        );
        sql_update_table('alumno', $sql_update, 'id_alumno', $alumno);
    }

    location('.');
}

if (request_var('submit', '')) {
    $grado   = request_var('grado', 0);
    $seccion = request_var('seccion', 0);
    $anio    = request_var('anio', 0);

    $grados  = get_grade_section($grado, $seccion);
    $alumnos = get_students_grade_section($grado, $seccion, $anio);

    _style('grade', [
        'NAME'    => $grados->nombre,
        'SECTION' => $grados->nombre_seccion
    ]);

    foreach ($alumnos as $i => $row) {
        if (!$i) {
            _style('results');
        }

        _style('results.row', $row);
    }
} else {
    $form = [[
        'grado' => [
            'type'  => 'select',
            'show'  => 'Grado',
            'value' => []
        ],
        'seccion' => [
            'type'  => 'select',
            'show'  => 'Curso',
            'value' => []
        ],
        'anio' => [
            'type'  => 'select',
            'show'  => 'A&ntilde;o',
            'value' => '*'
        ]
    ]];

    $grado   = get_grades();
    $seccion = get_sections();

    foreach ($grado as $row) {
        $form[0]['grado']['value'][$row->id_grado] = $row->nombre;
    }

    foreach ($seccion as $row) {
        $form[0]['seccion']['value'][$row->id_seccion] = $row->nombre_seccion;
    }

    _style('create', [
        'form'   => build_form($form),
        'submit' => build_submit()
    ]);
}

page_layout('C&oacute;digos de Alumnos', 'student_codes');
