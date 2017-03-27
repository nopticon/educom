<?php

require_once('../conexion.php');

if (request_var('submit', '')) {
    $area        = request_var('area', '');
    $observacion = request_var('observacion', '');

    if (empty($area)) {
        location('.');
    }

    $sql_insert = [
        'nombre_ocupacion' => $area,
        'observacion'      => $observacion
    ];
    sql_create('area_ocupacional', $sql_insert);

    location('.');
}

//
// List data
//
$sql = 'SELECT *
    FROM area_ocupacional';
$rowset = sql_rowset($sql);

foreach ($rowset as $i => $row) {
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
        'type'  => 'input',
        'value' => 'Nombre de &Aacute;rea'
    ],
    'observacion' => [
        'type'  => 'textarea',
        'value' => 'Observaci&oacute;n'
    ]
]];

_style('create_area', [
    'form'   => build_form($form),
    'submit' => build_submit()
]);

page_layout('Areas Ocupacionales', 'student_ocupational_areas');
