<?php

require_once('../conexion.php');

encabezado('Tareas');

$year = date('Y');
$create_form = false;
$user_role = get_user_role();
$is_post = request_method() == 'post';

if ($is_post) {
	switch ($user_role) {
		case 'student':
			break;
		case 'teacher':
			$fields = [
				'activity_name' => '',
				'activity_description' => '',
				'activity_start' => '',
				'activity_end' => '',
				'activity_schedule' => 0,
				'activity_grup' => 0,
				'activity_assignees' => '',
			];
			$fields = _request($fields);

			$fields->activity_assignees = json_decode($fields->activity_assignees);



			_pre($fields, true);
			break;
		case 'supervisor':
			break;
		case 'founder':
			break;
		default:
			exit;
			break;
	}
}

// 
// If request method is GET
// 
switch ($user_role) {
	case 'student':
		$group = get_user_grade($year);

		$sql = 'SELECT *
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

		foreach ($activity_list as $row) {

		}

		_pre($activity_list, true);
		break;
	case 'teacher':
		$create_form = true;

		$sql = 'SELECT u.id_curso, u.nombre_curso, g.id_grado, g.nombre, s.id_seccion, s.nombre_seccion
			FROM catedratico c
			INNER JOIN cursos u ON u.id_catedratico = c.id_catedratico
			INNER JOIN grado g ON g.id_grado = u.id_grado
			INNER JOIN secciones s ON g.id_grado = s.id_grado
			WHERE c.id_member = ?
			ORDER BY u.nombre_curso, s.nombre_seccion';
		if (!$teacher_schedule = sql_rowset(sql_filter($sql, $user->d('user_id')))) {
			echo 'Usted no tiene cursos asignados para crear tareas.';

			$create_form = false;
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

if ($create_form) {
	$form = array(
		'Crear tarea' => array(
			'activity_name' => array(
				'type' => 'text',
				'value' => 'T&iacute;tulo'
			),
			'activity_description' => array(
				'type' => 'textarea',
				'value' => 'Descripci&oacute;n'
			),
			'activity_start' => array(
				'type' => 'text',
				'value' => 'Fecha de Inicio'
			),
			'activity_end' => array(
				'type' => 'text',
				'value' => 'Fecha de Entrega'
			),
			'activity_schedule' => array(
				'type' => 'select',
				'show' => 'Materia',
				'value' => array()
			),
			'activity_grade' => array(
				'type' => 'select',
				'show' => 'Grado',
				'value' => array()
			),
			'activity_group' => array(
				'type' => 'select',
				'show' => 'Secci&oacute;n',
				'value' => array()
			),
			'activity_assignees' => [
				'type' => 'tags',
				'value' => 'Alumnos asignados'
			]
		)
	);

	foreach ($teacher_schedule as $row) {
		$form['Crear tarea']['activity_schedule']['value'][$row->id_curso] = $row->nombre_curso;
		$form['Crear tarea']['activity_grade']['value'][$row->id_grado] = $row->nombre;
		$form['Crear tarea']['activity_group']['value'][$row->id_seccion] = $row->nombre_seccion;
	}

	?>

	<form class="form-horizontal" action="<?php echo a('activity/'); ?>" method="post">
		<?php build($form); submit('Crear alumno'); ?>
	</form>
	
	<?php

}

pie();