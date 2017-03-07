<?php

require_once('../conexion.php');

if (request_var('submit2', '')) {
    $nombre_ocupacion = request_var('nombre_ocupacion', [0 => 0]);

    foreach ($nombre_ocupacion as $alumno => $codigo) {
        if (!$codigo) {
            continue;
        }

        $sql = 'SELECT *
            FROM ocupacion_alumno
            WHERE id_alumno = ?';
        if ($row = $db->sql_fieldrow(sql_filter($sql, $alumno))) {
            $sql = 'UPDATE ocupacion_alumno SET id_ocupacion = ?
                WHERE id_alumno = ?';
            $db->sql_query(sql_filter($sql, $codigo, $alumno));

            continue;
        }

        $sql_insert = array(
            'id_ocupacion' => $codigo,
            'id_alumno' => $alumno
        );
        $sql = 'INSERT INTO ocupacion_alumno' . $db->sql_build('INSERT', $sql_insert);
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
        WHERE g.id_grado = ?
            AND s.id_seccion = ?
            AND g.id_grado = s.id_grado';
    $grado_seccion = $db->sql_fieldrow(sql_filter($sql, $grado, $seccion));

    $sql = 'SELECT *
        FROM alumno a, grado g, reinscripcion r
        WHERE r.id_alumno = a.id_alumno
            AND g.id_grado = r.id_grado
            AND r.id_grado = ?
            AND r.id_seccion = ?
            AND r.anio = ?
        ORDER BY a.apellido, a.nombre_alumno ASC';
    $list = $db->sql_rowset(sql_filter($sql, $grado, $seccion, $anio));

    $sql = 'SELECT *
        FROM ocupacion_alumno';
    $ocup = $db->sql_rowset($sql, 'id_alumno', 'id_ocupacion');

    $sql = 'SELECT *
        FROM area_ocupacional';
    $area_ocupacional = $db->sql_rowset($sql);

    foreach ($list as $i => $row) {
        if (!$i) {
            _style('results', [
                'GRADE_NAME'   => $grado_seccion->nombre,
                'SECTION_NAME' => $grado_seccion->nombre_seccion
            ]);
        }

        _style('results.row', $row);

        foreach ($area_ocupacional as $area_row) {
            $area_row->select = (isset($ocup[$row->id_alumno]) && $ocup[$row->id_alumno] == $area_row->id_ocupacion);

            _style('results.row.select', $area_row);
        }
    }
} else {
    $sql = "SELECT id_grado, nombre
        FROM grado
        WHERE  status = 'Alta'";
    $grado = $db->sql_rowset($sql);

    $sql = 'SELECT id_seccion, nombre_seccion
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
            'show'  => 'Secci&oacute;n',
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
        'form'   => build_form($form),
        'submit' => build_submit()
    ]);
}

page_layout('Areas Ocupacionales', 'student_ocupational');
