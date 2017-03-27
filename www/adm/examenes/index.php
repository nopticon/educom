<?php

require_once('../conexion.php');

if (request_var('submit', '')) {
    $examen      = request_var('examen', '');
    $observacion = request_var('observacion', '');
    $status      = request_var('status', '');

    if (empty($examen)) {
        location('.');
    }

    $sql_insert = array(
        'examen'      => $examen,
        'observacion' => $observacion,
        'status'      => $status
    );
    sql_create('examenes', $sql_insert);

    location('.');
}

$sql = 'SELECT *
    FROM examenes
    ORDER BY id_examen';
$list = $db->sql_rowset($sql);

foreach ($list as $i => $row) {
    if (!$i) {
        _style('results');
    }

    _style('results.row', $row);
}

$form = [[
    'examen' => [
        'type'  => 'text',
        'value' => 'Unidad'
    ],
    'observacion' => [
        'type'  => 'text',
        'value' => 'Observaci&oacute;n'
    ],
    'status' => [
        'type'  => 'select',
        'show'  => 'Status',
        'value' => [
            'Alta' => 'Alta',
            'Baja' => 'Baja'
        ]
    ]
]];

_style('create', [
    'form'   => build_form($form),
    'submit' => build_submit()
]);

page_layout('Unidades', 'student_units');
