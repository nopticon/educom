<?php

require_once('../conexion.php');

if (request_var('submit2', '')) {
    $curso  = request_var('curso', 0);
    $examen = request_var('examen', 0);
    $grado  = request_var('grado', 0);
    $nota   = request_var('nota', [0 => 0]);

    foreach ($nota as $alumno => $nota) {
        $sql = 'SELECT *
            FROM notas
            WHERE id_alumno = ?
                AND id_grado = ?
                AND id_curso = ?
                AND id_bimestre = ?';
        if ($cada_nota = $db->sql_fieldrow(sql_filter($sql, $alumno, $grado, $curso, $examen))) {
            if (!$nota) {
                $sql = 'DELETE FROM notas
                    WHERE id_nota = ?';
                $db->sql_query(sql_filter($sql, $cada_nota['id_nota']));
            } else {
                $sql = 'UPDATE notas SET nota = ?
                    WHERE id_nota = ?';
                $db->sql_query(sql_filter($sql, $nota, $cada_nota['id_nota']));
            }

            continue;
        }

        if (!$nota) {
            continue;
        }

        $sql_insert = array(
            'id_alumno'   => $alumno,
            'id_grado'    => $grado,
            'id_curso'    => $curso,
            'id_bimestre' => $examen,
            'nota'        => $nota,
        );
        $sql = 'INSERT INTO notas' . $db->sql_build('INSERT', $sql_insert);
        $db->sql_query($sql);
    }

    location('.');
}

if (request_var('submit', '')) {
    $seccion = request_var('grado', 0);
    $curso   = request_var('curso', 0);
    $examen  = request_var('examen', 0);
    $anio    = request_var('anio', 0);

    $sql = 'SELECT id_grado, nombre_seccion
        FROM secciones
        WHERE id_seccion = ?';
    if (!$secciones = $db->sql_fieldrow(sql_filter($sql, $seccion))) {
        location('.');
    }

    $grado = $secciones->id_grado;

    $sql = 'SELECT *
        FROM grado
        WHERE id_grado = ?';
    if (!$grados = $db->sql_fieldrow(sql_filter($sql, $grado))) {
        location('.');
    }

    $sql = 'SELECT *
        FROM cursos
        WHERE id_curso = ?';
    $cursos = $db->sql_fieldrow(sql_filter($sql, $curso));

    $sql = 'SELECT *
        FROM examenes
        WHERE id_examen = ?';
    $examenes = $db->sql_fieldrow(sql_filter($sql, $examen));

    $sql = 'SELECT *
        FROM alumno a, grado g, reinscripcion r
        WHERE r.id_grado = g.id_grado
            AND r.id_alumno = a.id_alumno
            AND g.id_grado = ?
            AND r.id_seccion = ?
            AND r.anio = ?
        ORDER BY a.apellido, a.nombre_alumno';
    $reinscripcion = $db->sql_rowset(sql_filter($sql, $grado, $seccion, $anio));

    foreach ($reinscripcion as $i => $row) {
        $sql = 'SELECT *
            FROM notas
            WHERE id_alumno = ?
                AND id_grado = ?
                AND id_curso = ?
                AND id_bimestre = ?';
        $row->score = $db->sql_field(sql_filter($sql, $row->id_alumno, $grado, $curso, $examen), 'nota', '');

        if (!$i) _style('results', [
            'GRADE_NAME'    => $grados->nombre,
            'GRADE_SECTION' => $secciones->nombre_seccion,
            'UNIT_NAME'     => $examenes->examen,
            'COURSE_NAME'   => $cursos->nombre_curso,
            'YEAR'          => $anio,
            'GRADE_ID'      => $grado,
            'COURSE_ID'     => $curso,
            'UNIT_ID'       => $examenes->id_examen
        ]);

        _style('results.row', $row);
    }
} else {
    $sql = 'SELECT *
        FROM grado g, secciones s
        WHERE g.id_grado = s.id_grado
            AND status = ?';
    $grado = $db->sql_rowset(sql_filter($sql, 'Alta'));

    $sql = 'SELECT *
        FROM cursos
        WHERE id_grado = 1';
    $curso = $db->sql_rowset($sql);

    $sql = 'SELECT *
        FROM examenes';
    $examen = $db->sql_rowset($sql);

    $form = [[
        'grado' => [
            'type'  => 'select',
            'show'  => 'Grado',
            'value' => []
        ],
        'curso' => [
            'type'  => 'select',
            'show'  => 'Curso',
            'value' => []
        ],
        'examen' => [
            'type'  => 'select',
            'show'  => 'Examen',
            'value' => []
        ],
        'anio' => [
            'type'  => 'select',
            'show'  => 'A&ntilde;o',
            'value' => '*'
        ]
    ]];

    foreach ($grado as $row) {
        $form[0]['grado']['value'][$row->id_seccion] = $row->nombre . ' - ' . $row->nombre_seccion;
    }

    foreach ($curso as $row) {
        $form[0]['curso']['value'][$row->id_curso] = $row->nombre_curso;
    }

    foreach ($examen as $row) {
        $form[0]['examen']['value'][$row->id_examen] = $row->examen;
    }

    _style('query', [
        'form'   => build_form($form),
        'submit' => build_submit()
    ]);
}

page_layout('Edici&oacute;n de Notas', 'student_scores_edit');
