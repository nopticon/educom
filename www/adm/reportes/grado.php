<?php

require_once('../conexion.php');

$id_alumno = request_var('id_alumno', 0);
$id_grado  = request_var('id_grado', 0);

encabezado_simple('Grado');

$sql = 'SELECT *
    FROM reinscripcion r, secciones s, grado g, alumno a
    WHERE g.id_grado = ?
        AND r.id_alumno = ?
        AND r.id_seccion = s.id_seccion
        AND r.id_alumno = a.id_alumno
        AND r.id_grado = g.id_grado';
if (!$reinscripcion = $db->sql_fieldrow(sql_filter($sql, $id_grado, $id_alumno))) {
    location('../historial/');
}

$sql = 'SELECT *
    FROM secciones s, grado g
    WHERE s.id_seccion = ?
        AND s.id_grado = g.id_grado';
$secciones = $db->sql_fieldrow(sql_filter($sql, $reinscripcion->id_seccion));

$sql = 'SELECT *
    FROM examenes
    ORDER BY id_examen';
$examenes = $db->sql_rowset($sql);

?>

<table width="100%">
    <tr>
        <td width="111">&nbsp;</td>
        <td width="127" class="text1" align="right">Carn&eacute;:</td>
        <td width="325" class="textred"><?php echo $reinscripcion->carne; ?></td>
        <td width="73" class="text1" align="right">Fecha:</td>
        <td width="146" class="text2"><?php echo $reinscripcion->fecha; ?></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td><div align="right" class="text1">Nombres y Apellidos: </div></td>
        <td class="textblack"><?php echo $reinscripcion->nombre_alumno . ' ' . $reinscripcion->apellido; ?></td>
        <td><div align="right" class="text1">Telefono:</div></td>
        <td class="textblack"><?php echo $reinscripcion->telefono1; ?></td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td><div align="right" class="text1">Email: </div></td>
        <td class="textblack"><?php echo $reinscripcion->email; ?></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
  <td><div align="right" class="text1">Grado:</div></td>
        <td class="textblack"><?php echo $reinscripcion->nombre . ', secci&oacute;n: ' . $reinscripcion->nombre_seccion; ?></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
    <tr>
        <td>&nbsp;</td>
        <td><div align="right" class="text1">Encargado:</div></td>
        <td class="textblack"><?php echo $reinscripcion->encargado_reinscripcion; ?></td>
        <td>&nbsp;</td>
        <td>&nbsp;</td>
    </tr>
</table>

    <br />
    <table width="95%" border="1" align="center" style="border-collapse:collapse">
        <tr>
            <td class="a_center Estilo6" width="25%">Curso</td>
            <?php

            foreach ($examenes as $row) {
                echo '<td class="a_center" width="15%">' . $row->examen . '</td>';
            }

            ?>
        </tr>

        <?php

        $sql = 'SELECT *
            FROM alumno a, grado g, cursos c, reinscripcion r
            WHERE a.id_alumno = ?
                AND g.id_grado = ?
                AND r.id_grado = g.id_grado
                AND r.id_alumno = a.id_alumno
                AND g.id_grado = c.id_grado';
        $alumno_grado = $db->sql_rowset(sql_filter($sql, $id_alumno, $id_grado));

        foreach ($alumno_grado as $row) {

        ?>
        <tr>
            <td class="text1"><?php echo $row->nombre_curso; ?></td>
            <?php

            foreach ($examenes as $row_examenes) {
                $sql = 'SELECT *
                    FROM notas
                    WHERE id_alumno = ?
                        AND id_grado = ?
                        AND id_curso = ?
                        AND id_bimestre = ?';
                // _pre($row_examenes);
                // _pre($row, true);
                $nota = $db->sql_field(sql_filter($sql, $row->id_alumno, $row->id_grado, $row->id_curso, $row_examenes->id_examen), 'nota', false);

                echo '<td class="a_center" width="15%">' . $nota . '</td>';
            }

            ?>
        </tr>
        <?php

        }
        ?>
        </table>

        <br />
        <table width="95%" align="center">
            <tr>
                <td>
                <span class="textred"><strong>Observaciones:</strong></span><br />
                <span class="textblack">
                De 0 a 59 puntos, reprobado.<br />
                De 60 a 100 puntos, aprobado.<br /><br /></span>

        <strong>Faltas acad&eacute;micas:</strong>
        <ul>
        <?php

        $sql = 'SELECT *
            FROM faltas
            WHERE id_alumno = ?
            ORDER BY fecha_falta DESC
            LIMIT 3';
        if ($faltas = $db->sql_rowset(sql_filter($sql, $row->id_alumno))) {
            foreach ($faltas as $row_falta) {
                echo '<li>' . $row_falta->falta . '</li>';
            }
        } else {
            echo '<li>No hay faltas.</li>';
        }

        ?>
        </ul>

        <br /><br />
        <div class="a_center">Vo. Bo.<br /><br />DIRECTOR</div>

        <br />
        <hr />

        <span><p>Se&ntilde;or Director:</p></span>
        <span><p>Yo <strong><?php echo $reinscripcion->encargado_reinscripcion; ?></strong> por este medio hago constar que he quedado
        enterado de las calificaciones de mi hijo(a): <strong><?php echo $reinscripcion->nombre_alumno . ' ' . $reinscripcion->apellido; ?></strong>
        que cursa el <?php echo $secciones->nombre; ?>, seccion: <?php echo $secciones->nombre_seccion; ?>.
        <p class="a_right">Fecha: <?php echo date('d m Y'); ?></p></span>
        <p align="left">(f) _____________________________________________<br />Padre de familia o Encargado</p>
                </td>
            </tr>
        </table>

        <br />
    </form>
</div>
</div>

<?php pie(); ?>
