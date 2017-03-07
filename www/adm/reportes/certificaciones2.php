 <?php

define('XFS', '../');

require_once('../conexion.php');
require_once(XFS . 'pdf/pdf.php');
require_once(XFS . 'pdf/convert.php');

$cv  = new convert();
$pdf = new _pdf('LEGAL');

$pdf->cp->selectFont(XFS . 'pdf/helvetica.afm');

$page_count = 0;
$coord_sum  = 0;

$id_seccion = request_var('id_seccion', 0);
$alumno     = request_var('alumno', 0);
$anio       = request_var('anio', 0);

$sql = 'SELECT *
    FROM secciones s, grado g
    WHERE s.id_seccion = ?
        AND s.id_grado = g.id_grado';
$secciones = $db->sql_fieldrow(sql_filter($sql, $id_seccion));

$sql = 'SELECT * FROM reinscripcion r, secciones s, grado g, alumno a
    WHERE r.id_grado = ?
        AND r.id_seccion = ?
        AND r.anio = ?
        AND r.id_seccion = s.id_seccion
        AND r.id_alumno = a.id_alumno
        AND r.id_grado = g.id_grado';
$sql = sql_filter($sql, $secciones->id_grado, $secciones->id_seccion, $anio);

if ($alumno) {
    $sql2 = 'SELECT id_alumno
        FROM alumno
        WHERE id_alumno = ?';
    if ($db->sql_field(sql_filter($sql2, $alumno))) {
        $sql .= sql_filter(' AND a.id_alumno = ?', $alumno);
    }
}

$list = $db->sql_rowset($sql);

$i = 0;
foreach ($list as $row) {
    if ($i) $pdf->new_page();

    $pdf->cp->addJpegFromFile('../public/images/logo-cert.jpg', 65, $pdf->cp->cy(125), 400);

    $grado = ucfirst(strtolower(implode(' ', array_splice(explode(' ', $row->nombre), 0, 1))));
    $firma1 = '';
    $firma2 = '';

    switch ($row->id_grado) {
        case 1:
            $firma1 = 'Candida Rosa Flores Alvarado';
            $firma2 = 'Oficinista II';
            $inicio = 'La infrascrita';
             break;
        case 2:
            $firma1 = 'Azar&iacute;as Isa&iacute; Hoil Franco';
            $firma2 = 'Oficinista II';
            $inicio = 'El infrascrito';
            break;
        case 3:
        case 4:
            $firma1 = 'Gladys Marieta S&aacute;nchez Deluca';
            $firma2 = 'Oficinista II';
            $inicio = 'La infrascrita';
            break;
    }

    $text_block = ''. $inicio .' Oficinista II, del Instituto Nacional de Educaci&oacute;n B&aacute;sica, Adscrito a la Escuela Normal Rural No. 5 &quot;Prof. Julio Edmundo Rosado Pinelo&quot; de Santa Elena, Pet&eacute;n. Acuerdo Ministerial No. 994 del 10/07/85.';

    $text_block2 = 'CERTIFICA: Que el alumno (a): ' . $row->nombre_alumno . ' ' . $row->apellido;
    $text_block4 = 'Durante el Ciclo Escolar ' . $anio . ' curs&oacute; el ' . $grado . ' Grado de Cultura General B&aacute;sica, con C&oacute;digo Personal No. ' . $row->codigo_alumno . '. Extendido por el Ministerio de Educaci&oacute;n en la ciudad de Guatemala, y que ha tenido a la vista los Cuadros de Registro de Evaluaci&oacute;n en donde aparece que se hizo acreedor(a) a las notas siguientes:';

    $pdf->text_wrap($text_block, 11, $pdf->page_width() - 140, 65, $pdf->top(150), 20, 'full', false, 40);
    $pdf->text_wrap($text_block2, 11, $pdf->page_width() - 140, 65, $pdf->top(65), 20);
    $pdf->text_wrap($text_block4, 11, $pdf->page_width() - 140, 65, $pdf->top(25), 20, 'full', false, 40);

    $_areas = array();
    $infot = array(array(array('text' => 'No.', 'align' => 'center', 'width' => 30)));

    switch ($row->id_grado) {
        case 3:
            break;
        default:
            $infot[0][] = array('text' => 'Areas', 'align' => 'center', 'width' => 105);
            break;
    }

    $infot[0][] = array('text' => 'Curso', 'align' => 'center');
    $infot[0][] = array('text' => 'No.', 'align' => 'center', 'width' => 30);
    $infot[0][] = array('text' => 'Nota en letras', 'align' => 'center');
    $infot[0][] = array('text' => 'Resultado', 'align' => 'center', 'width' => 75);

    $sql11 = 'SELECT *
        FROM area_ocupacional a, ocupacion_alumno oc
        WHERE a.id_ocupacion = oc.id_ocupacion
            AND oc.id_alumno = ?';
    $ocupacion = $db->sql_fieldrow(sql_filter($sql11, $row->id_alumno));

    $sql = 'SELECT *
        FROM cursos c, areas_cursos ac, reinscripcion r
        WHERE r.id_grado = ?
            AND r.id_seccion = ?
            AND r.id_alumno = ?
            AND r.anio = ?
            AND r.id_grado = c.id_grado
            AND c.id_area = ac.id_area';
    $data = $db->sql_rowset(sql_filter($sql, $secciones->id_grado, $secciones->id_seccion, $row->id_alumno, $anio));

    $j = 1;
    foreach ($data as $data_row) {
        $per_curse = $per_curse_f = 0;

        $sql = 'SELECT *
            FROM examenes
            WHERE examen NOT LIKE ?
            ORDER BY id_examen';
        $examenes = $db->sql_rowset(sql_filter($sql, '%Recup%'));

        foreach ($examenes as $examenes_row) {
            $sql = 'SELECT *
                FROM notas
                WHERE id_alumno = ?
                    AND id_grado = ?
                    AND id_curso = ?
                    AND id_bimestre = ?';
            $notas = $db->sql_fieldrow(sql_filter($sql, $row->id_alumno, $row->id_grado, $data_row->id_curso, $examenes_row->id_examen));

            if (!isset($notas->nota)) $notas->nota = 0;
            if (!isset($notas->nota2)) $notas->nota2 = 0;

            $total = $notas->nota + $notas->nota2;

            $per_curse += $total;

            if ($total) $per_curse_f++;
        }

        if (!$per_curse_f) $per_curse_f = 1;

        $per_sum = number_format($per_curse / $per_curse_f, 0);

        $resultado = ($per_sum >= 60) ? 'Aprobado' : 'No aprobado';

        if ($per_sum) {
            $lets = ucfirst($cv->cv($per_sum));
        } else {
            $resultado = '';
            $lets = '';
        }

        if ($data_row->nombre_curso == 'Ocupacional' && isset($ocupacion->nombre_ocupacion)) {
            $data_row->nombre_curso = $ocupacion->nombre_ocupacion;
        }

        $infot[$j] = array(array('text' => $j, 'align' => 'center'));

        switch ($row->id_grado) {
            case 3:
                break;
            default:
                $_merge = false;
                if (in_array($data_row->nombre_area, $_areas)) {
                    $data_row->nombre_area = '';
                    $_merge = true;
                }

                $_areas[] = $data_row->nombre_area;
                $infot[$j][] = array('text' => $data_row->nombre_area, 'align' => 'center', 'merge' => $_merge);
                break;
        }

        $infot[$j][] = array('text' => $data_row->nombre_curso, 'align' => 'left');
        $infot[$j][] = array('text' => $per_sum, 'align' => 'center');
        $infot[$j][] = array('text' => $lets, 'align' => 'left');
        $infot[$j][] = array('text' => $resultado, 'align' => 'center');

        $j++;
    }

    $pdf->multitable($infot, 65, $pdf->top(100), 5, 9, 1, array('last_height' => $pdf->top()));

    switch ($anio) {
        case 2010:
            $day_string = 'quince';
            break;
        case 2011:
            $day_string = 'catorce';
            break;
        case 2011:
            $day_string = 'quince';
            break;
    }

    $text_block = 'En fe de lo anterior se extiende el presente certificado en Santa Elena de la Cruz, Flores, Pet&eacute;n, a los ' . $day_string . ' d&iacute;as del mes de octubre del ' . $cv->cv($anio) . '.';

    $pdf->text_wrap($text_block, 11, $pdf->page_width() - 185, 65, $pdf->top(50), 20);

    $names = array(array(
        array('text' => $firma1, 'align' => 'center'),
        array('text' => 'Vo. Bo. Lic. Baldomero Fidel Ram&iacute;rez Zabala', 'align' => 'center')
    ),
    array(
        array('text' => $firma2, 'align' => 'center'),
        array('text' => 'Director', 'align' => 'center')
    ));

    $pdf->multitable($names, 35, $pdf->top(100), 5, 11, 0);

    $i++;
}

$pdf->cp->ezOutput();
$pdf->cp->stream();

exit;
