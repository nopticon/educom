<?php

define('XFS', '../');

require_once('../conexion.php');
require_once(XFS . 'pdf/pdf.php');

$id_seccion = request_var('seccion', 0);
$id_examen  = request_var('examen', 0);
$anio       = request_var('anio', 0);

$sql = 'SELECT *
    FROM secciones s, grado g
    WHERE s.id_seccion = ?
        AND s.id_grado = g.id_grado';
$secciones = $db->sql_fieldrow(sql_filter($sql, $id_seccion));

$sql = 'SELECT *
    FROM examenes
    WHERE id_examen = ?';
$examenes = $db->sql_fieldrow(sql_filter($sql, $id_examen));

//
// Start PDF
//
$pdf = new _pdf('LEGAL', 'landscape');
$pdf->cp->selectFont(XFS . 'pdf/helvetica.afm');

$page_count = 0;
$coord_sum = 0;

$str_grado = 'Grado: ' . $secciones->nombre . ' ' . $secciones->nombre_seccion;
$str_examen = 'Tiempo de examen: ' . $examenes->examen;

$pdf->text(35, $pdf->top(25), $str_grado, 12);
$pdf->text(200, $pdf->top(25, true), $str_examen, 12);

/*
$pdf->cp->line(15, $pdf->cp->cy(10), $pdf->page_width(15), $pdf->cp->cy(10));
$pdf->cp->line(15, 17, $pdf->page_width(15), 17);
$pdf->cp->line(15, $pdf->cp->cy(10), 15, 17);
$pdf->cp->line($pdf->page_width(15), $pdf->cp->cy(10), $pdf->page_width(15), 17);
*/

//$pdf->new_page();

$sql = 'SELECT *
    FROM cursos
    WHERE id_grado = ?
    ORDER BY id_curso';
$cursos = $db->sql_rowset(sql_filter($sql, $secciones->id_grado));

$ls_cursos = $id_cursos = $table = array();
$i = 0;

$table[] = array(
    array('text' => 'Carne', 'align' => 'center', 'width' => 75),
    array('text' => 'Alumnos', 'align' => 'center')
);

foreach ($cursos as $row) {
    $i++;

    $table[0][] = array('text' => $i, 'align' => 'center', 'width' => 35);

    $ls_cursos[] = array('text' => $i . ' = ' . $row->nombre_curso, 'align' => 'left');

    $id_cursos[$row->id_curso] = $row->nombre_curso;
}

$table[0][] = array(
    'text'  => 'P',
    'align' => 'center',
    'width' => 40
);

$sql = 'SELECT *
    FROM reinscripcion r, alumno a
    WHERE r.id_alumno = a.id_alumno
        AND r.id_grado = ?
        AND r.id_seccion = ?
        AND r.anio = ?
    ORDER BY a.apellido, a.nombre_alumno';
$reinscripcion = $db->sql_rowset(sql_filter($sql, $secciones->id_grado, $secciones->id_seccion, $anio));

foreach ($reinscripcion as $row) {
    $alumno = array(
        array(
            'text'  => $row->carne,
            'align' => 'left'
        ),
        array(
            'text'  => $row->apellido . ', ' . $row->nombre_alumno,
            'align' => 'left'
        )
    );

    $numeros = array();
    foreach ($id_cursos as $id => $nombre) {
        $sql = 'SELECT nota
            FROM notas
            WHERE id_alumno = ?
                AND id_grado = ?
                AND id_curso = ?
                AND id_bimestre = ?';
        $nota = $db->sql_field(sql_filter($sql, $row->id_alumno, $secciones->id_grado, $id, $id_examen), 'nota', false);

        $numeros[] = array('text' => $nota, 'align' => 'center');
    }

    $fill = 0;
    $sum = 0;
    foreach ($numeros as $numero) {
        if ($numero['text']) {
            $fill++;
            $sum += $numero['text'];
        }
    }

    $text_numeros = ($fill) ? number_format(round(($sum / $fill), 2), 2) : '';

    $numeros[] = array(
        'text' => $text_numeros,
        'align' => 'center'
    );

    $table[] = array_merge($alumno, $numeros);
}

$pdf->dynamic_table($ls_cursos, 35, 30, 5, 4, 10, 0);

$pdf->multitable($table, 35, 125, 5, 10, 1, array('last_height' => $pdf->top()));

$pdf->cp->ezOutput();
$pdf->cp->stream();

exit;
