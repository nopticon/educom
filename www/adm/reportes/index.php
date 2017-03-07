<?php

require_once('../conexion.php');

$page_title = 'Reportes del sistema';

$list = [
    './alumnos/listado_alumno.php'           => 'Listado de Alumnos',
    // './asistencia/listado_alumno.php'        => 'Control Asistencia de Alumnos',
    // './promedios/'                           => 'Promedios de Alumnos',
    // './calificaciones.php'                   => 'Tarjeta de Calificaciones',
    './catedraticos/listado_catedratico.php' => 'Catedr&aacute;ticos con Cursos',
    // './certificaciones.php'                  => 'Certificaciones Anuales',
    // './fgenerales.php'                       => 'Cuadros Generales de Calificaciones',
    // './carta_editar.php'                     => 'Carta para Edici&oacute;n de Calificaci&oacute;n',
];

$i = 0;
foreach ($list as $url => $title) {
    if (!$i) {
        _style('list', [
            'title' => $page_title
        ]);
    }

    _style('list.row', [
        'url'   => $url,
        'title' => $title
    ]);

    $i++;
}

page_layout($page_title, 'student_list');
