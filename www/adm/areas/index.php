<?php

require_once('../conexion.php');

if (request_var('submit', '')) {
    $area        = request_var('area', '');
    $observacion = request_var('observacion', '');

    if (empty($area)) {
        location('.');
    }

    $sql_insert = [
        'nombre_area' => $area,
        'observacion_area' => $observacion
    ];
    $sql = 'INSERT INTO areas_cursos' . $db->sql_build('INSERT', $sql_insert);
    $db->sql_query($sql);

    location('.');
}

//
// Get data
//
$sql = 'SELECT *
    FROM areas_cursos';
$list = $db->sql_rowset($sql);

foreach ($list as $i => $row) {
    if (!$i) {
        _style('results');
    }

    _style('results.row', $row);
}

//
// Create form
//
$form = [[
    'area' => [
        'type' => 'input',
        'value' => 'Nombre de &Aacute;rea'
    ],
    'observacion' => [
        'type' => 'textarea',
        'value' => 'Observaci&oacute;n'
    ]
]];

_style('create_area', [
    'form'   => build_form($form),
    'submit' => build_submit()
]);

page_layout('Creacion de Areas', 'student_areas');
