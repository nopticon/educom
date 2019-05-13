<?php

require_once('../../conexion.php');

encabezado('Listado de alumnos');

$grados    = get_grades();
$secciones = get_sections();

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
    'anio' => [
        'type'  => 'select',
        'show'  => 'A&ntilde;o',
        'value' => '*'
    ]
]];

foreach ($grados as $row) {
    $form[0]['grado']['value'][$row->id_grado] = $row->nombre;
}

foreach ($secciones as $row) {
    $form[0]['seccion']['value'][$row->id_seccion] = $row->nombre_seccion;
}

?>

<form class="form-horizontal" action="listado_alumno1.php" method="post">
    <?php build($form); submit(); ?>
</form>

<?php pie(); ?>
