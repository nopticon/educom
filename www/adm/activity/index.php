<?php

require_once('../conexion.php');

$year        = date('Y');
$create_form = false;
$user_role   = get_user_role();
$is_post     = request_method() == 'post';

switch ($user_role) {
    case 'student':
        if ($is_post) {
            //
        }

        //
        // GET Method
        //
        _style($user_role, [
            'title' => 'Tareas ' . $year
        ]);

        $activity_list = get_student_own_tasks();

        foreach ($activity_list as $i => $row) {
            if (!$i) {
                _style([$user_role, 'activities']);
            }

            foreach (w('start end') as $field) {
                $row->{'activity_' . $field} = $user->format_date(strtotime($row->{'activity_' . $field}), 'l, ' . lang('date_format'));
            }

            _style([$user_role, 'activities', 'row'], $row);
        }
        break;
    case 'teacher':
        if ($is_post) {
            $fields = [
                'activity_name'        => '',
                'activity_description' => '',
                'activity_start'       => '',
                'activity_end'         => '',
                'activity_schedule'    => 0,
                'activity_group'       => 0,
                'activity_assignees'   => [
                    'default' => '',
                    'filter'  => ['html_entity_decode', 'json_decode']
                ],
            ];
            $fields = _request($fields);

            //
            // Look up students assignees
            //
            if ($fields->activity_assignees) {
                $lookup_assignees = get_students_for_tasks($fields->activity_assignees);
            } else {
                $lookup_assignees = get_students_for_tasks(false, $fields->activity_group);
            }

            //
            // Insert task
            //
            $sql_insert = array(
                'activity_name'        => $fields->activity_name,
                'activity_description' => $fields->activity_description,
                'activity_start'       => $fields->activity_start,
                'activity_end'         => $fields->activity_end,
                'activity_schedule'    => $fields->activity_schedule,
                'activity_group'       => $fields->activity_group
            );
            $task_id = create_teacher_activity($sql_insert);

            foreach ($lookup_assignees as $row) {
                create_student_activity($task_id, $row->user_id);
            }

            $_SESSION['activity_message'] = 'La tarea fue creada correctamente.';
        }

        //
        // GET Method
        //
        _style($user_role);

        $assigned_grades = get_teacher_grade_section();

        $sql = 'SELECT u.id_curso, u.nombre_curso, g.id_grado, g.nombre, s.id_seccion, s.nombre_seccion
            FROM catedratico c
            INNER JOIN cursos u ON u.id_catedratico = c.id_member
            INNER JOIN grado g ON g.id_grado = u.id_grado
            INNER JOIN secciones s ON g.id_grado = s.id_grado
            WHERE c.id_member = ?
            ORDER BY u.nombre_curso, s.nombre_seccion';
        if ($teacher_schedule = sql_rowset(sql_filter($sql, $user->d('user_id')))) {
            $form = [
                'Crear tarea' => [
                    'activity_name' => [
                        'type'  => 'text',
                        'value' => 'T&iacute;tulo'
                    ],
                    'activity_description' => [
                        'type'  => 'textarea',
                        'value' => 'Descripci&oacute;n'
                    ],
                    'activity_start' => [
                        'type'  => 'calendar',
                        'value' => 'Fecha de Inicio'
                    ],
                    'activity_end' => [
                        'type'  => 'calendar',
                        'value' => 'Fecha de Entrega'
                    ],
                    'activity_group' => [
                        'type'  => 'select',
                        'show'  => 'Grado / Secci&oacute;n',
                        'value' => []
                    ],
                    'activity_schedule' => [
                        'type'  => 'select',
                        'show'  => 'Materia',
                        'value' => []
                    ],
                    'activity_assignees' => [
                        'type'  => 'tags',
                        'value' => 'Alumnos asignados'
                    ]
                ]
            ];

            $form['Crear tarea']['activity_group']['value'][] = 'Seleccione el grado y secci&oacute;n';

            foreach ($assigned_grades as $row) {
                $form['Crear tarea']['activity_group']['value'][$row->id_seccion] = $row->nombre . ' - ' . $row->nombre_seccion;
            }

            $form['Crear tarea']['activity_schedule']['value'][] = 'Seleccione el curso';

            $activity_message = '';
            if (isset($_SESSION['activity_message']) && $_SESSION['activity_message']) {
                $activity_message = $_SESSION['activity_message'];
                unset($_SESSION['activity_message']);
            }

            _style('teacher.create_activity', [
                'message' => $activity_message,
                'form'    => build_form($form),
                'submit'  => build_submit('Guardar informaci&oacute;n')
            ]);
        } else {
            _style('teacher.no_courses_assigned');
        }

        //
        // List of tasks created by current teacher
        //
        if ($tasks = get_teacher_own_tasks()) {
            foreach ($tasks as $i => $row) {
                if (!$i) {
                    _style('current_tasks');
                }

                foreach (w('start end') as $field) {
                    $row->{'activity_' . $field} = $user->format_date(strtotime($row->{'activity_' . $field}), lang('date_format'));
                }

                $row->url = s_link('today', ['task', $row->assigned_id]);

                _style('current_tasks.row', $row);
            }
        }
        break;
    case 'supervisor':
        break;
    case 'founder':
        break;
    default:
        exit;
        break;
}

page_layout('Tareas', 'activities');
