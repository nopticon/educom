<?php

require_once('../../conexion.php');

$grado   = request_var('grado', 0);
$seccion = request_var('seccion', 0);
$examen  = request_var('examen', 0);
$anio    = request_var('anio', 0);

encabezado('Promedio de Alumnos');

$sql = 'SELECT *
    FROM grado g, secciones s
    WHERE g.id_grado = ?
        AND s.id_seccion = ?
        AND g.id_grado = s.id_grado';
$grado_seccion = $db->sql_fieldrow(sql_filter($sql, $grado, $seccion));

$sql = 'SELECT *
    FROM examenes
    WHERE id_examen = ?';
$examenes = $db->sql_fieldrow(sql_filter($sql, $examen));

$sql = 'SELECT *, AVG(n.nota) AS promedio
    FROM alumno a, grado g, reinscripcion r, notas n, cursos c
    WHERE r.id_grado = ?
        AND r.id_seccion = ?
        AND r.anio = ?
        AND n.id_bimestre = ?

        AND g.id_grado = r.id_grado
        AND r.id_alumno = a.id_alumno
        AND n.id_alumno = r.id_alumno
        AND n.id_grado = r.id_grado
        AND c.id_curso = n.id_curso
    GROUP BY a.id_alumno
    ORDER BY promedio DESC';
$list = $db->sql_rowset(sql_filter($sql, $grado, $seccion, $anio, $examen));

?>

<br />
<table class="table table-bordered">
    <tr>
        <td>Grado: <?php echo $grado_seccion->nombre . ' ' . $grado_seccion->nombre_seccion; ?></td>
        <td>Unidad: <?php echo $examenes->examen; ?></td>
    </tr>
</table>

<br />
<table class="table table-striped">
    <thead>
        <tr>
            <td>#</td>
            <td>Carn&eacute;</td>
            <td>Apellido</td>
            <td>Nombre</td>
            <td>Promedio</td>
        </tr>
    </thead>
    <tbody>
        <?php

        foreach ($list as $i => $row) {
            $row->promedio = number_format(round($row->promedio, 2), 2);
            $highlight = ($row->promedio >= 60) ? '' : 'danger';

        ?>
        <tr class="<?php echo $highlight; ?>">
            <th scope="row"><?php echo ($i + 1); ?></td>
            <td><?php echo $row->carne; ?></td>
            <td><?php echo $row->apellido; ?></td>
            <td><?php echo $row->nombre_alumno; ?></td>
            <td class="a_center"><?php echo $row->promedio; ?></td>
        </tr>
        <?php } ?>
    </tbody>
</table>

<?php pie(); ?>
