<?php

require_once('../../conexion.php');

encabezado('Promedio de Alumnos', '../');

$sql = "SELECT *
    FROM grado
    WHERE status = 'Alta'";
$grado = $db->sql_rowset($sql);

$sql = 'SELECT *
    FROM secciones
    WHERE id_grado = 1';
$secciones = $db->sql_rowset($sql);

$sql = 'SELECT *
    FROM examenes';
$examenes = $db->sql_rowset($sql);

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
    'examen' => [
        'type'  => 'select',
        'show'  => 'Unidad',
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

foreach ($secciones as $row) {
    $form[0]['seccion']['value'][$row->id_seccion] = $row->nombre_seccion;
}

foreach ($examenes as $row) {
    $form[0]['examen']['value'][$row->id_examen] = $row->examen;
}

?>

<form class="form-horizontal" action="listado_alumno1.php" method="post">
    <?php build($form); submit(); ?>
</form>

<?php pie();
