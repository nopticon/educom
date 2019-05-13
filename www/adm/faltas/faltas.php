<?php

require_once('../conexion.php');

$carne = request_var('carne', '');

if (!$alumno = get_student_info($carne)) {
    location('.');
}

encabezado('Ingreso de Faltas Acad&eacute;micas');

$courses = get_assigned_courses($alumno->id_alumno);

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
