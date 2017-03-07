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
        $sql = 'UPDATE alumno SET' . $db->sql_build('UPDATE', $sql_update) . sql_filter('
            WHERE id_alumno = ?', $alumno);
        $db->sql_query($sql);
    }

    location('.');
}

if (request_var('submit', '')) {
    $grado   = request_var('grado', 0);
    $seccion = request_var('seccion', 0);
    $anio    = request_var('anio', 0);

    $sql = 'SELECT *
        FROM grado g, secciones s
        WHERE g.id_grado = s.id_grado
            AND g.id_grado = ?
            AND s.id_seccion = ?';
    $grados = $db->sql_fieldrow(sql_filter($sql, $grado, $seccion));

    $sql = 'SELECT *
        FROM alumno a, grado g, reinscripcion r
        WHERE r.id_alumno = a.id_alumno
            AND g.id_grado = r.id_grado
            AND r.id_grado = ?
            AND r.id_seccion = ?
            AND r.anio = ?
        ORDER BY a.apellido, a.nombre_alumno ASC';
    $alumnos = $db->sql_rowset(sql_filter($sql, $grado, $seccion, $anio));

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
    $sql = 'SELECT *
        FROM grado
        WHERE status = ?';
    $grado = $db->sql_rowset(sql_filter($sql, 'Alta'));

    $sql = 'SELECT *
        FROM secciones
        WHERE id_grado = 1';
    $seccion = $db->sql_rowset($sql);

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

    foreach ($grado as $row) {
        $form[0]['grado']['value'][$row->id_grado] = $row->nombre;
    }

    foreach ($seccion as $row) {
        $form[0]['seccion']['value'][$row->id_seccion] = $row->nombre_seccion;
    }

    _style('create', [
        'form' => build_form($form),
        'submit' => build_submit()
    ]);
}

page_layout('C&oacute;digos de Alumnos', 'student_codes');
