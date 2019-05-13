<?php

require_once('../../conexion.php');

$carne = request_var('carne', '');

if (!$alumno = get_student_by_id($carne, false, 'carne')) {
    location('.');
}

encabezado('Mantenimiento Alumno', '../');

$form = [
    'Datos de Alumno' => [
        'Carne' => [
            'type'    => 'text',
            'value'   => 'Carn&eacute;',
            'default' => $alumno->carne
        ],
        'CodigoAlumno' => [
            'type'    => 'text',
            'value'   => 'C&oacute;digo de alumno',
            'default' => $alumno->codigo_alumno
        ],
        'Nombre' => [
            'type'    => 'text',
            'value'   => 'Nombre',
            'default' => $alumno->nombre_alumno
        ],
        'Apellido' => [
            'type'    => 'text',
            'value'   => 'Apellido',
            'default' => $alumno->apellido
        ],
        'Direccion' => [
            'type'    => 'text',
            'value'   => 'Direcci&oacute;n',
            'default' => $alumno->direccion
        ],
        'Telefono' => [
            'type'    => 'text',
            'value'   => 'Tel&eacute;fono',
            'default' => $alumno->telefono1
        ],
        'Edad' => [
            'type'    => 'text',
            'value'   => 'Edad',
            'default' => $alumno->edad
        ],
        'Email' => [
            'type'    => 'text',
            'value'   => 'Email',
            'default' => $alumno->email
        ],
        'Sexo' => [
            'type'  => 'radio',
            'value' => [
                1 => 'Masculino',
                2 => 'Femenino'
            ],
            'default' => $alumno->sexo
        ]
    ],
    'Datos de Padres' => [
        'Padre' => [
            'type'    => 'text',
            'value'   => 'Padre',
            'default' => $alumno->padre
        ],
        'Madre' => [
            'type'    => 'text',
            'value'   => 'Madre',
            'default' => $alumno->madre
        ]
    ],
    'Datos de Encargado' => [
        'Encargado' => [
            'type'    => 'text',
            'value'   => 'Encargado',
            'default' => $alumno->encargado
        ],
        'Profesion' => [
            'type'    => 'text',
            'value'   => 'Profesi&oacute;n o oficio',
            'default' => $alumno->profesion
        ],
        'Labor' => [
            'type'    => 'text',
            'value'   => 'Lugar de trabajo',
            'default' => $alumno->labora
        ],
        'Direccion2' => [
            'type'    => 'text',
            'value'   => 'Direcci&oacute;n',
            'default' => $alumno->direccion_labora
        ],
        'DPI' => [
            'type'    => 'text',
            'value'   => 'DPI',
            'default' => $alumno->dpi
        ],
        'Extendida' => [
            'type'    => 'text',
            'value'   => 'Extendido',
            'default' => $alumno->extendida
        ]
    ],
    'En caso de emergencia' => [
        'Emergencia' => [
            'type' => 'radio',
            'value' => [
                'Padre'     => 'Padre',
                'Madre'     => 'Madre',
                'Encargado' => 'Encargado',
            ],
            'default' => $alumno->emergencia
        ],
        'Telefono2' => [
            'type'    => 'text',
            'value'   => 'Tel&eacute;fonos',
            'default' => $alumno->telefono2
        ]
    ]
];

?>

<br />
<form class="form-horizontal" action="mantenimientos/cod_mant/cod_man_alumno.php" method="post">
    <input name="id_alumno" type="hidden" id="id_alumno" value="<?php echo $alumno->id_alumno; ?>" />

    <?php build($form); submit('Guardar cambios'); ?>
</form>

<?php pie();
