<?php

require_once('../conexion.php');

$page_title = 'Mantenimiento del sistema';

$list = [
    'alumnos/index.php'             => 'Alumnos',
    '../examenes/index.php'         => 'Unidades',
    '../areas/index.php'            => 'Areas',
    'cursos/index.php'              => 'Cursos',
    '../grados/index.php'           => 'Grados',
    '../secciones/index.php'        => 'Secciones',
    '../catedraticos/index.php'     => 'Catedr&aacute;ticos',
    '../cursos/index.php'           => 'Cursos, grados y catedr&aacute;ticos',
    '../area_ocupacional/index.php' => 'Areas Ocupacionales',
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
