<?php

require_once('../../conexion.php');

encabezado('Mantenimiento de Alumnos');

$form = [[
    'carne' => [
        'type'  => 'text',
        'value' => 'Carn&eacute;'
    ]
]];

?>

<form class="form-horizontal" action="alumno.php" method="post">
    <?php build($form); submit(); ?>
</form>

<?php pie();
