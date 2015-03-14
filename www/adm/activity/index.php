<?php

require_once('../conexion.php');

$year 			= date('Y');
$create_form 	= false;
$user_role 		= get_user_role();
$is_post 		= request_method() == 'post';

switch ($user_role) {
	case 'student':
		if ($is_post) {
			// 
		}

		_style($user_role, [
			'title' => 'Tareas ' . $year
		]);

		// 
		// GET Method
		// 
		$group = get_user_grade($year);

		$sql = 'SELECT *, c.apellido as apellido_catedratico
			FROM alumno a, reinscripcion r, _activities ac, _activities_assigned aa, 
				catedratico c, grado g, secciones s, areas_cursos acu, cursos cu
			WHERE a.id_member = ?
				AND aa.assigned_student = a.id_member
				AND a.id_alumno = r.id_alumno
				AND aa.assigned_activity = ac.activity_id
				AND c.id_member = ac.activity_teacher
				AND s.id_grado = g.id_grado
				AND s.id_seccion = ac.activity_group
				AND acu.id_area = cu.id_area
				AND ac.activity_schedule = cu.id_curso';
		$activity_list = sql_rowset(sql_filter($sql, $user->d('user_id')));

		foreach ($activity_list as $i => $row) {
			if (!$i) _style([$user_role, 'activities']);

			foreach (w('start end') as $field) {
				$row->{'activity_' . $field} = $user->format_date(strtotime($row->{'activity_' . $field}), 'l, ' . lang('date_format'));
			}

			_style([$user_role, 'activities', 'row'], $row);
		}
		break;
	case 'teacher':
		if ($is_post) {
			$fields = [
				'activity_name' => '',
				'activity_description' => '',
				'activity_start' => '',
				'activity_end' => '',
				'activity_schedule' => 0,
				'activity_grup' => 0,
				'activity_assignees' => [
					'default' => '',
					'filter' => ['html_entity_decode', 'json_decode']
				],
			];
			$fields = _request($fields);

			// 
			// Look up students assignees
			// 
			$sql = 'SELECT user_id, username
				FROM _members
				WHERE username IN (' . implode(', ', array_fill(0, count($fields->activity_assignees), '?')) . ')
				ORDER BY user_id';
			$lookup_assignees = sql_rowset(sql_filter($sql, $fields->activity_assignees), 'user_id', 'username');

			_pre($lookup_assignees);
			_pre($fields, true);
		}

		// 
		// GET Method
		// 
		_style($user_role);

		$sql = 'SELECT u.id_curso, u.nombre_curso, g.id_grado, g.nombre, s.id_seccion, s.nombre_seccion
			FROM catedratico c
			INNER JOIN cursos u ON u.id_catedratico = c.id_catedratico
			INNER JOIN grado g ON g.id_grado = u.id_grado
			INNER JOIN secciones s ON g.id_grado = s.id_grado
			WHERE c.id_member = ?
			ORDER BY u.nombre_curso, s.nombre_seccion';
		if ($teacher_schedule = sql_rowset(sql_filter($sql, $user->d('user_id')))) {
			$form = [
				'Crear tarea' => [
					'activity_name' => [
						'type' => 'text',
						'value' => 'T&iacute;tulo'
					],
					'activity_description' => [
						'type' => 'textarea',
						'value' => 'Descripci&oacute;n'
					],
					'activity_start' => [
						'type' => 'calendar',
						'value' => 'Fecha de Inicio'
					],
					'activity_end' => [
						'type' => 'calendar',
						'value' => 'Fecha de Entrega'
					],
					'activity_schedule' => [
						'type' => 'select',
						'show' => 'Materia',
						'value' => []
					],
					'activity_grade' => [
						'type' => 'select',
						'show' => 'Grado',
						'value' => []
					],
					'activity_group' => [
						'type' => 'select',
						'show' => 'Secci&oacute;n',
						'value' => []
					],
					'activity_assignees' => [
						'type' => 'tags',
						'value' => 'Alumnos asignados'
					]
				]
			];

			foreach ($teacher_schedule as $row) {
				$form['Crear tarea']['activity_schedule']['value'][$row->id_curso] = $row->nombre_curso;
				$form['Crear tarea']['activity_grade']['value'][$row->id_grado] = $row->nombre;
				$form['Crear tarea']['activity_group']['value'][$row->id_seccion] = $row->nombre_seccion;
			}

			_style('teacher.create_activity', [
				'form' => build_form($form),
				'submit' => build_submit('Crear alumno')
			]);
		} else {
			_style('teacher.no_courses_assigned');
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