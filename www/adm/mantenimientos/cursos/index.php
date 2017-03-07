<?php

require_once('../../conexion.php');

encabezado('Modificaci&oacute;n de Cursos');

$sql = "SELECT *
    FROM grado
    WHERE status = 'Alta'";
$grado = $db->sql_rowset($sql);

$form = array(
    array(
        'grado' => array(
            'type'  => 'select',
            'show'  => 'Grado',
            'value' => array()
        )
    )
);

foreach ($grado as $row) {
    $form[0]['grado']['value'][$row->id_grado] = $row->nombre;
}

?>

<form class="form-horizontal" action="cursos.php" method="post">
    <?php build($form); submit(); ?>
</form>

<?php pie();
