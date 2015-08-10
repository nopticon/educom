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
				'activity_group' => 0,
				'activity_assignees' => [
					'default' => '',
					'filter' => ['html_entity_decode', 'json_decode']
				],
			];
			$fields = _request($fields);

			// 
			// Look up students assignees
			// 
			if ($fields->activity_assignees) {
				$sql = 'SELECT user_id, username
					FROM _members
					WHERE username IN (' . implode(', ', array_fill(0, count($fields->activity_assignees), '?')) . ')
					ORDER BY user_id';
				$lookup_assignees = sql_rowset(sql_filter($sql, $fields->activity_assignees), 'user_id', 'username');
			} else {
				$sql = 'SELECT m.user_id, m.username
					FROM _members m
					INNER JOIN alumno a ON m.user_id = a.id_member
					INNER JOIN reinscripcion r ON r.id_alumno = a.id_alumno
					WHERE r.id_seccion = ?
						AND r.anio = ?
					ORDER BY m.username';
				$lookup_assignees = sql_rowset(sql_filter($sql, $fields->activity_group, date('Y')));
			}

			$now = date('Y-m-d H:i:s');

			// 
			// Insert task
			// 
			$sql_insert = array(
				'activity_name' => $fields->activity_name,
				'activity_description' => $fields->activity_description,
				'activity_start' => date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $fields->activity_start) . ' +6 hours')),
				'activity_end' => date('Y-m-d H:i:s', strtotime(str_replace('/', '-', $fields->activity_end) . ' +6 hours')),
				'activity_show' => 1,
				'activity_teacher' => $user->d('user_id'),
				'activity_schedule' => $fields->activity_schedule,
				'activity_group' => $fields->activity_group,
				'activity_ip' => $user->ip,
				'created_at' => $now,
				'updated_at' => $now,
			);

			$task_id = sql_insert('activities', $sql_insert);

			foreach ($lookup_assignees as $row) {
				$sql_insert = array(
					'assigned_activity' => $task_id,
					'assigned_student' => $row->user_id,
					'assigned_delivered' => 0,
					'assigned_total' => 0,
					'assigned_comments' => 1,
					'created_at' => $now,
					'updated_at' => $now
				);
				$assigned_id = sql_insert('activities_assigned', $sql_insert);
			}

			$_SESSION['activity_message'] = 'La tarea fue creada correctamente.';
		}

		// 
		// GET Method
		// 
		_style($user_role);

		$sql = 'SELECT DISTINCT g.id_grado, g.nombre, s.id_seccion, s.nombre_seccion
			FROM catedratico c
			INNER JOIN cursos u ON u.id_catedratico = c.id_catedratico
			INNER JOIN grado g ON g.id_grado = u.id_grado
			INNER JOIN secciones s ON g.id_grado = s.id_grado
			WHERE c.id_member = ?
			ORDER BY g.id_grado';
		$assigned_grades = sql_rowset(sql_filter($sql, $user->d('user_id')));

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
					'activity_group' => [
						'type' => 'select',
						'show' => 'Grado / Secci&oacute;n',
						'value' => []
					],
					'activity_schedule' => [
						'type' => 'select',
						'show' => 'Materia',
						'value' => []
					],
					'activity_assignees' => [
						'type' => 'tags',
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
				'form' => build_form($form),
				'submit' => build_submit('Guardar informaci&oacute;n')
			]);
		} else {
			_style('teacher.no_courses_assigned');
		}

		//
		// List of tasks created by current teacher
		//
		$sql = 'SELECT *
			FROM _activities a
			INNER JOIN cursos u ON a.activity_schedule = u.id_curso
			INNER JOIN secciones s ON a.activity_group = s.id_seccion
			INNER JOIN grado g ON s.id_grado = g.id_grado
			WHERE activity_teacher = ?
			ORDER BY a.created_at DESC';
		if ($tasks = sql_rowset(sql_filter($sql, $user->d('user_id')))) {
			foreach ($tasks as $i => $row) {
				if (!$i) _style('current_tasks');

				foreach (w('start end') as $field) {
					$row->{'activity_' . $field} = $user->format_date(strtotime($row->{'activity_' . $field}), lang('date_format'));
				}

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