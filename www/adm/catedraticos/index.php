<?php

require_once('../conexion.php');

if (request_var('submit', '')) {
    $nombre      = request_var('nombre', '');
    $apellido    = request_var('apellido', '');
    $profesion   = request_var('profesion', '');
    $email       = request_var('email', '');
    $telefonos   = request_var('telefonos', '');
    $direccion   = request_var('direccion', '');
    $observacion = request_var('observacion', '');
    $gender      = request_var('gender', 0);

    $registro = '';
    $status = 'Alta';

    if (empty($nombre) || empty($apellido)) {
        location('.');
    }

    $member_data = array(
        'username'      => [$nombre, $apellido],
        'user_email'    => $email,
        'user_gender'   => $gender
    );
    $user_id = create_user_account($member_data);

    $sql_insert = array(
        'id_member'          => $user_id,
        'registro'           => $registro,
        'nombre_catedratico' => $nombre,
        'apellido'           => $apellido,
        'profesion'          => $profesion,
        'email'              => $email,
        'telefono'           => $telefonos,
        'direccion'          => $direccion,
        'observacion'        => $observacion,
        'status'             => $status
    );
    sql_create('catedratico', $sql_insert);

    $cache->delete('team_teacher');

    location('.');
}

$can_edit = $user->is('founder');
$can_edit = true;

$catedraticos = get_all_teachers();

foreach ($catedraticos as $i => $row) {
    if (!$i) {
        _style('results', [
            'can_edit' => $can_edit
        ]);
    }

    $row->u_edit = '../mantenimientos/catedraticos/mod_catedratico.php?id_catedratico=' . $row->id_catedratico;

    _style('results.row', $row);
}

$form = [[
    'nombre' => [
        'type'  => 'input',
        'value' => 'Nombre'
    ],
    'apellido' => [
        'type'  => 'input',
        'value' => 'Apellido'
    ],
    'profesion' => [
        'type'  => 'input',
        'value' => 'Profesi&oacute;n'
    ],
    'email' => [
        'type'  => 'input',
        'value' => 'Email'
    ],
    'telefonos' => [
        'type'  => 'input',
        'value' => 'Tel&eacute;fonos'
    ],
    'direccion' => [
        'type'  => 'input',
        'value' => 'Direcci&oacute;n'
    ],
    'observacion' => [
        'type'  => 'text',
        'value' => 'Observaci&oacute;n'
    ]
]];

_style('create', [
    'form'   => build_form($form),
    'submit' => build_submit()
]);

page_layout('Catedr&aacute;ticos', 'teacher');
