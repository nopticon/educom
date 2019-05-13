<?php

require_once('../conexion.php');

if (request_var('submit', '')) {
    $grado  = request_var('grado', '');
    $status = request_var('status', '');

    if (empty($grado)) {
        location('.');
    }

    $sql_insert = array(
        'nombre'      => $grado,
        'status'      => $status,
        'seccion'     => '',
        'fecha_grado' => get_now()
    );
    sql_create('grado', $sql_insert);

    location('.');
}

$can_edit = $user->is('founder');

$list = get_grades();

foreach ($list as $i => $row) {
    if (!$i) {
        _style('results', [
            'can_edit' => $can_edit
        ]);
    }

    $row->u_edit = '../mantenimientos/grados/mod_grados.php?id_grado=' . $row->id_grado;

    _style('results.row', $row);
}

$form = [[
    'grado' => [
        'type'  => 'text',
        'value' => 'Nombre de grado'
    ],
    'status' => [
        'type'  => 'select',
        'show'  => 'Estado',
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

page_layout('Grados', 'student_grades');
