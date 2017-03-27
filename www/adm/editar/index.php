<?php

require_once('../conexion.php');

if (request_var('submit2', '')) {
    $curso  = request_var('curso', 0);
    $examen = request_var('examen', 0);
    $grado  = request_var('grado', 0);
    $scores = request_var('nota', [0 => 0]);

    foreach ($scores as $alumno => $nota) {
        if ($current_score = get_student_single_score($alumno, $grado, $curso, $examen)) {
            if (!$nota) {
                do_score_remove($current_score->id_nota);
            } else {
                do_score_update($current_score->id_nota, $nota);
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
        sql_create('notas', $sql_insert);
    }

    location('.');
}

if (request_var('submit', '')) {
    $seccion = request_var('grado', 0);
    $curso   = request_var('curso', 0);
    $examen  = request_var('examen', 0);
    $anio    = request_var('anio', 0);

    if (!$secciones = get_section($seccion)) {
        location('.');
    }

    $grado = $secciones->id_grado;

    if (!$grados = get_grade($grado)) {
        location('.');
    }

    $cursos   = get_course($curso);
    $examenes = get_exam_group($examen);
    $students = get_students_grade_section($grado, $seccion, $anio);

    foreach ($students as $i => $row) {
        $row->score = '';
        if ($score = get_student_single_score($row->id_alumno, $grado, $curso, $examen)) {
            $row->score = $score->nota;
        }

        if (!$i) {
            _style('results', [
                'GRADE_NAME'    => $grados->nombre,
                'GRADE_SECTION' => $secciones->nombre_seccion,
                'UNIT_NAME'     => $examenes->examen,
                'COURSE_NAME'   => $cursos->nombre_curso,
                'YEAR'          => $anio,
                'GRADE_ID'      => $grado,
                'COURSE_ID'     => $curso,
                'UNIT_ID'       => $examenes->id_examen
            ]);
        }

        _style('results.row', $row);
    }
} else {
    $grado  = get_grades_sections();
    $curso  = get_grade_courses();
    $examen = get_all_exams();

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
