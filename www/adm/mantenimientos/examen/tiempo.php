<?php

require_once('../../conexion.php');

$id_examen = request_var('id_examen', 0);

encabezado('Modificaci&oacute;n de Unidad');

$sql = 'SELECT *
    FROM examenes
    WHERE id_examen = ?';
$examen = $db->sql_fieldrow(sql_filter($sql, $id_examen));

$form = [[
    'examen' => [
        'type'    => 'text',
        'value'   => 'Unidad',
        'default' => $examen->examen
    ],
    'observacion' => [
        'type'    => 'text',
        'value'   => 'Observaciones',
        'default' => $examen->observacion
    ],
    'status' => [
        'type' => 'select',
        'show' => 'Status',
        'value' => [
            'Alta' => 'Alta',
            'Baja' => 'Baja'
        ],
        'default' => $examen->status
    ]
]];

?>

<form class="form-horizontal" action="../cod_mant/cod_man_examen.php" method="post">
    <input name="id_examen" type="hidden" value="<?php echo $id_examen; ?>" />

    <?php build($form); submit(); ?>
</form>

<?php pie(); ?>
