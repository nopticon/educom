<?php

require_once('../conexion.php');

$carne = request_var('carne', '');

$sql = 'SELECT a.id_alumno, a.carne, a.nombre_alumno, a.apellido, g.nombre AS nombre_grado, s.nombre_seccion
    FROM alumno a
    INNER JOIN reinscripcion r ON r.id_alumno = a.id_alumno
    INNER JOIN grado g ON g.id_grado = r.id_grado
    INNER JOIN secciones s ON s.id_seccion = r.id_seccion
    WHERE a.carne = ?
        AND r.anio = ?';
if (!$alumno = $db->sql_fieldrow(sql_filter($sql, $carne, date('Y')))) {
    location('.');
}

encabezado('Ingreso de Faltas Acad&eacute;micas');

//
// Get assigned courses for student
//
$sql = 'SELECT c.id_curso, c.nombre_curso
    FROM cursos c
    INNER JOIN reinscripcion r ON r.id_seccion = c.id_section
    INNER JOIN catedratico a ON a.id_catedratico = c.id_catedratico
    WHERE r.id_alumno = ?
        AND a.id_member = ?';
$courses = sql_rowset(sql_filter($sql, $alumno->id_alumno, $user->d('user_id')));

?>

<form class="form-horizontal" action="cod_falta.php" method="post">
    <input type="hidden" name="id_alumno" value="<?php echo $alumno->id_alumno; ?>" />
    <input type="hidden" name="teacher_id" value="<?php echo $user->d('user_id'); ?>" />

    <table class="table table-bordered">
        <tbody>
            <tr>
                <td>Carn&eacute;</td>
                <td><?php echo $alumno->carne; ?></td>
            </tr>
            <tr>
                <td>Nombre</td>
                <td><?php echo $alumno->nombre_alumno; ?></td>
            </tr>
            <?php if ($alumno->apellido) { ?>
            <tr>
                <td>Apellido</td>
                <td><?php echo $alumno->apellido; ?></td>
            </tr>
            <?php } ?>
            <tr>
                <td>Grado y secci&oacute;n</td>
                <td><?php echo $alumno->nombre_grado . ' ' . $alumno->nombre_seccion; ?></td>
            </tr>
        </tbody>
    </table>

    <div class="form-group">
        <label for="inputfaultdate" class="col-lg-2 control-label">Fecha</label>
        <div class="col-lg-10">
            <div class="input-group date">
                <input name="fault_date" id="inputfaultdate" placeholder="Fecha" value="<?php echo date('d/m/Y'); ?>" type="text" class="form-control"><span class="input-group-addon"><i class="glyphicon glyphicon-th"></i></span>
            </div>
        </div>
    </div>

    <div class="form-group">
        <label for="inputTipo" class="col-lg-2 control-label">Curso</label>
        <div class="col-lg-10">
            <select name="course_id" class="width-lg-select">
                <?php foreach ($courses as $row) { ?>
                <option value="<?php echo $row->id_curso; ?>"><?php echo $row->nombre_curso; ?></option>
                <?php } ?>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="inputTipo" class="col-lg-2 control-label">Tipo de falta</label>
        <div class="col-lg-10">
            <select name="tipo_falta" id="tipo_falta">
                <option value="Baja">Baja</option>
                <option value="Media">Media</option>
                <option value="Alta">Alta</option>
            </select>
        </div>
    </div>

    <div class="form-group">
        <label for="inputFalta" class="col-lg-2 control-label">Descripci&oacute;n</label>
        <div class="col-lg-10">
            <textarea name="fault_description" class="form-control" id="inputFalta"></textarea>
        </div>
    </div>

    <div class="text-center"><?php submit(); ?></div>
</form>

<?php pie();
