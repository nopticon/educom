<?php

require_once('../conexion.php');

if (request_var('submit', '')) {
    $curso        = request_var('curso', '');
    $capacidad    = request_var('capacidad', 0);
    $grado        = request_var('grado', 0);
    $catedratico  = request_var('catedratico', 0);
    $areas_cursos = request_var('areas_cursos', 0);
    $status       = 'Alta';

    $sql_insert = array(
        'id_area'        => $areas_cursos,
        'nombre_curso'   => $curso,
        'capacidad'      => $capacidad,
        'status'         => $status,
        'id_grado'       => $grado,
        'id_catedratico' => $catedratico,
    );
    sql_create('cursos', $sql_insert);

    location('.');
}

$areas_cursos = get_area_courses();
$grado        = get_grades();
$catedratico  = get_all_teachers();
$relacion     = get_course_grades_teachers();

$form = [[
    'areas_cursos' => [
        'type'  => 'select',
        'show'  => '&Aacute;reas',
        'value' => []
    ],
    'curso' => [
        'type'  => 'text',
        'value' => 'Nombre de Curso'
    ],
    'capacidad' => [
        'type'  => 'text',
        'value' => 'Capacidad'
    ],
    'grado' => [
        'type'  => 'select',
        'show'  => 'Grado',
        'value' => []
    ],
    'catedratico' => [
        'type'  => 'select',
        'show'  => 'Catedr&aacute;tico',
        'value' => []
    ]
]];

foreach ($areas_cursos as $row) {
    $form[0]['areas_cursos']['value'][$row->id_area] = $row->nombre_area;
}

foreach ($grado as $row) {
    $form[0]['grado']['value'][$row->id_grado] = $row->nombre;
}

foreach ($catedratico as $row) {
    $form[0]['catedratico']['value'][$row->id_catedratico] = $row->nombre_catedratico . ' ' . $row->apellido;
}

foreach ($relacion as $i => $row) {
    if (!$i) {
        _style('results');
    }

    _style('results.row', $row);
}

_style('create', [
    'form'   => build_form($form),
    'submit' => build_submit()
]);

page_layout('Cursos para Grado', 'student_courses');
