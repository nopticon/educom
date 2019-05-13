<?php

require_once('../conexion.php');

$action = request_var('action', '');
$year = date('Y');

switch ($action) {
    case 'created':
        _style('student_created');

        $recent_students = get_recent_students();

        foreach ($recent_students as $i => $row) {
            if (!$i) {
                _style('student_created.recent');
            }

            _style('student_created.recent.row', $row);
        }
        break;
    default:
        //
        // Create fields
        //
        $form = array(
            'Datos de Alumno' => array(
                'codigo_alumno' => array(
                    'type'  => 'text',
                    'value' => 'C&oacute;digo de alumno'
                ),
                'nombre' => array(
                    'type'  => 'text',
                    'value' => 'Nombre'
                ),
                'apellido' => array(
                    'type'  => 'text',
                    'value' => 'Apellido'
                ),
                'direccion' => array(
                    'type'  => 'text',
                    'value' => 'Direcci&oacute;n'
                ),
                'telefono' => array(
                    'type'  => 'text',
                    'value' => 'Tel&eacute;fono'
                ),
                'edad' => array(
                    'type'  => 'text',
                    'value' => 'Edad'
                ),
                'email' => array(
                    'type'  => 'text',
                    'value' => 'Email'
                ),
                'sexo' => array(
                    'show'  => 'Sexo',
                    'type'  => 'select',
                    'value' => array(
                        'M' => 'Masculino',
                        'F' => 'Femenino'
                    )
                ),
            ),
            'Datos de Padres' => array(
                'padre' => array(
                    'type'  => 'text',
                    'value' => 'Padre'
                ),
                'madre' => array(
                    'type'  => 'text',
                    'value' => 'Madre'
                ),
            ),
            'Datos de Encargado' => array(
                'encargado' => array(
                    'type'  => 'text',
                    'value' => 'Encargado'
                ),
                'profesion' => array(
                    'type'  => 'text',
                    'value' => 'Profesi&oacute;n o oficio'
                ),
                'labor' => array(
                    'type'  => 'text',
                    'value' => 'Lugar de trabajo'
                ),
                'email_encargado' => array(
                    'type'  => 'text',
                    'value' => 'Email'
                ),
                'direccion2' => array(
                    'type'  => 'text',
                    'value' => 'Direcci&oacute;n'
                ),
                'dpi' => array(
                    'type'  => 'text',
                    'value' => 'DPI'
                ),
                'extendido' => array(
                    'type'  => 'text',
                    'value' => 'Extendido'
                ),
            ),
            'En caso de emergencia' => array(
                'emergencia' => array(
                    'show' => 'Llamar a',
                    'type' => 'select',
                    'value' => array(
                        'Encargado' => 'Encargado',
                        'Padre'     => 'Padre',
                        'Madre'     => 'Madre',
                    )
                ),
                'telefono2' => array(
                    'type'  => 'text',
                    'value' => 'Tel&eacute;fonos'
                ),
            ),
            'Inscripci&oacute;n ' . $year => array(
                'grado' => array(
                    'type'  => 'select',
                    'show'  => 'Grado',
                    'value' => array()
                ),
                'seccion' => array(
                    'type'  => 'select',
                    'show'  => 'Secci&oacute;n',
                    'value' => array()
                )
            )
        );

        $grado   = get_grades();
        $seccion = get_sections();

        foreach ($grado as $row) {
            $form['Inscripci&oacute;n ' . $year]['grado']['value'][$row->id_grado] = $row->nombre;
        }

        foreach ($seccion as $row) {
            $form['Inscripci&oacute;n ' . $year]['seccion']['value'][$row->id_seccion] = $row->nombre_seccion;
        }

        _style('create_student', [
            'form'   => build_form($form),
            'submit' => build_submit('Crear alumno')
        ]);
        break;
}

if (request_var('submit', '')) {
    $nombre           = request_var('nombre', '');
    $apellido         = request_var('apellido', '');
    $direccion        = request_var('direccion', '');
    $telefono1        = request_var('telefono', '');
    $edad             = request_var('edad', '');
    $sexo             = request_var('sexo', '');
    $email            = request_var('email', '');

    $padre            = request_var('padre', '');
    $madre            = request_var('madre', '');

    $encargado        = request_var('encargado', '');
    $profesion        = request_var('profesion', '');
    $labor            = request_var('labor', '');
    $direccion_labora = request_var('direccion2', '');
    $encargado_email  = request_var('email_encargado', '');
    $dpi              = request_var('dpi', '');
    $extendido        = request_var('extendido', '');

    $emergencia       = request_var('emergencia', '');
    $telefono2        = request_var('telefono2', '');

    $grado            = request_var('grado', 0);
    $seccion          = request_var('seccion', 0);

    $status           = 'Inscrito';

    //
    // Process information
    //
    if (empty($nombre) || empty($apellido)) {
        location('.');
    }

    //
    // Build array to insert
    //
    $insert_alumno = array(
        'codigo_alumno'    => $codigo,
        'nombre_alumno'    => $nombre,
        'apellido'         => $apellido,
        'direccion'        => $direccion,
        'telefono1'        => $telefono1,
        'edad'             => $edad,
        'sexo'             => $sexo,
        'email'            => $email,
        'padre'            => $padre,
        'madre'            => $madre,
        'encargado'        => $encargado,
        'profesion'        => $profesion,
        'labora'           => $labor,
        'direccion_labora' => $direccion_labora,
        'email_encargado'  => $encargado_email,
        'dpi'              => $dpi,
        'extendida'        => $extendido,
        'emergencia'       => $emergencia,
        'telefono2'        => $telefono2
    );
    $student_info = create_student_info($insert_alumno);

    $insert_inscripcion = array(
        'id_alumno'               => $student_info['student_id'],
        'carne'                   => $student_info['student_carne'],
        'id_grado'                => $grado,
        'id_seccion'              => $seccion,
        'encargado_reinscripcion' => $encargado,
        'telefonos'               => $telefono2
    );
    $reinscription_id = create_current_student($insert_inscripcion);

    //
    // Insert user into main system.
    //
    $member_data = array(
        'username'    => $nombre . ' ' . $apellido,
        'user_email'  => $email,
        'user_gender' => choose_gender($sexo)
    );
    $user_id = create_user_account($member_data);

    $update_alumno = array(
        'id_member' => $user_id
    );
    update_student_info($student_id, $update_alumno);

    //
    // Create user login for supervisor
    //
    if ($encargado) {
        if (!$supervisor_id = get_user_id($encargado)) {
            $supervisor_data = array(
                'username'   => $encargado,
                'user_email' => $encargado_email
            );
            $supervisor_id = create_user_account($supervisor_data);
        }

        $rel_id = create_student_supervisor($supervisor_id, $user_id);
    }

    location('.?action=created');
}

page_layout('Crear alumno', 'student_inscription');
