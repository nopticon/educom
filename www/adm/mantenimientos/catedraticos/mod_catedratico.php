<?php

require_once('../../conexion.php');

$id_catedratico = request_var('id_catedratico', 0);

$sql = 'SELECT *
    FROM catedratico
    WHERE id_catedratico = ?';
if (!$catedratico = $db->sql_fieldrow(sql_filter($sql, $id_catedratico))) {
    location('../../catedraticos/');
}

encabezado('Modificaci&oacute;n de Catedr&aacute;tico');

$form = [[
    'nombre' => [
        'type'    => 'text',
        'value'   => 'Nombre',
        'default' => $catedratico->nombre_catedratico
    ],
    'apellido' => [
        'type'    => 'text',
        'value'   => 'Apellido',
        'default' => $catedratico->apellido
    ],
    'profesion' => [
        'type'    => 'text',
        'value'   => 'Profesi&oacute;n',
        'default' => $catedratico->profesion
    ],
    'email' => [
        'type'    => 'text',
        'value'   => 'Correo electr&oacute;nico',
        'default' => $catedratico->email
    ],
    'telefonos' => [
        'type'    => 'text',
        'value'   => 'Tel&eacute;fono',
        'default' => $catedratico->telefono
    ],
    'direccion' => [
        'type'    => 'text',
        'value'   => 'Direcci&oacute;n',
        'default' => $catedratico->direccion
    ],
    'observacion' => [
        'type'    => 'text',
        'value'   => 'Observaci&oacute;n',
        'default' => $catedratico->observacion
    ]
]];

?>

<form class="form-horizontal" action="../cod_mant/cod_man_catedratico.php" method="post">
    <input name="id_catedratico" type="hidden" value="<?php echo $catedratico->id_catedratico; ?>" />

    <?php build($form); submit(); ?>
</form>

<?php pie();
