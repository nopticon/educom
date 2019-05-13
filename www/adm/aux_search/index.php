<?php

require_once('../conexion.php');

$firstname = request_var('nombre', '');
$lastname  = request_var('apellido', '');

//
// Search for given firstname and lastname
//
if ($firstname || $lastname) {
    if ($alumnos = search_wildcard_students($firstname, $lastname)) {
        foreach ($alumnos as $i => $row) {
            if (!$i) {
                _style('results');
            }

            _style('results.row', $row);
        }
    } else {
        _style('no_results');
    }
}

//
// Create form
//
$form = [[
    'nombre' => [
        'type'  => 'input',
        'value' => 'Nombres'
    ],
    'apellido' => [
        'type'  => 'input',
        'value' => 'Apellido'
    ]
]];

_style('search_student', [
    'form'   => build_form($form),
    'submit' => build_submit()
]);

page_layout('Busqueda de Alumno', 'student_search');
