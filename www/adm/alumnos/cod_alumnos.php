<?php

require_once("../conexion.php");

$codigo = request_var('codigo_alumno', '');
$nombre = request_var('nombre', '');
$apellido = request_var('apellido', '');
$direccion = request_var('direccion', '');
$telefono1 = request_var('telefono', '');
$edad = request_var('edad', '');
$sexo = request_var('sexo', '');
$email = request_var('email', '');

$padre = request_var('padre', '');
$madre = request_var('madre', '');

$encargado = request_var('encargado', '');
$profesion = request_var('profesion', '');
$labor = request_var('labor', '');
$direccion_labora = request_var('direccion2', '');
$encargado_email = request_var('email_encargado', '');
$dpi = request_var('dpi', '');
$extendido = request_var('extendido', '');

$emergencia = request_var('emergencia', '');
$telefono2 = request_var('telefono2', '');

$grado = request_var('grado', 0);
$seccion = request_var('seccion', 0);

$status = 'Inscrito';
$anio = date('Y');
$carne = $anio . $sexo;

// 
// Process information
// 
if (!$nombre || !$apellido) {
	redirect('/adm/alumnos/index.php');
}

$edad = sprintf("%02d", $edad);

// 
// Build array to insert
// 
$insert_alumno = array(
	'carne' => $carne,
	'codigo_alumno' => $codigo,
	'nombre_alumno' => $nombre,
	'apellido' => $apellido,
	'direccion' => $direccion,
	'orden' => '',
	'registro' => '',
	'telefono1' => $telefono1,
	'edad' => $edad,
	'sexo' => $sexo,
	'email' => $email,
	'padre' => $padre,
	'madre' => $madre,
	'encargado' => $encargado,
	'profesion' => $profesion,
	'labora' => $labor,
	'direccion_labora' => $direccion_labora,
	'email_encargado' => $encargado_email,
	'dpi' => $dpi,
	'extendida' => $extendido,
	'emergencia' => $emergencia,
	'telefono2' => $telefono2,
	'status' => $status
);
$student_id = sql_create('alumno', $insert_alumno);

// 
// Add student id to carne
// 
$carne .= $student_id;

$insert_inscripcion = array(
	'id_alumno' => $student_id,
	'carne' => $carne,
	'id_grado' => $grado,
	'id_seccion' => $seccion,
	'encargado_reinscripcion' => $encargado,
	'telefonos' => $telefono2,
	'status' => $status,
	'anio' => $anio
);
$reinscription_id = sql_create('reinscripcion', $insert_inscripcion);

// 
// Insert user into main system.
// 
$gender_select = array(
	'M' => 1,
	'F' => 2
);
$gender = isset($gender_select[$sexo]) ? $gender_select[$sexo] : 1;

$country = 90;
$birthdate = '';

$full_name = $nombre . ' ' . $apellido;
$username_base = simple_alias($full_name);
$user_password = substr(md5(unique_id()), 0, 8);

$member_data = array(
	'user_type' => USER_NORMAL,
	'user_active' => 1,
	'username' => $full_name,
	'username_base' => $username_base,
	'user_password' => HashPassword($user_password),
	'user_regip' => $user->ip,
	'user_session_time' => 0,
	'user_lastpage' => '',
	'user_lastvisit' => time(),
	'user_regdate' => time(),
	'user_level' => 0,
	'user_posts' => 0,
	'userpage_posts' => 0,
	'user_points' => 0,
	'user_timezone' => $config->board_timezone,
	'user_dst' => $config->board_dst,
	'user_lang' => $config->default_lang,
	'user_dateformat' => $config->default_dateformat,
	'user_country' => $country,
	'user_rank' => 0,
	'user_avatar' => '',
	'user_avatar_type' => 0,
	'user_email' => $email,
	'user_lastlogon' => 0,
	'user_totaltime' => 0,
	'user_totallogon' => 0,
	'user_totalpages' => 0,
	'user_gender' => $gender,
	'user_birthday' => $birthdate,
	'user_upw' => $user_password,
	'user_mark_items' => 0,
	'user_topic_order' => 0,
	'user_email_dc' => 1,
	'user_refop' => 0,
	'user_refby' => ''
);
$user_id = sql_insert('members', $member_data);

set_config('max_users', $config->max_users + 1);

$update_alumno = array(
	'carne' => $carne,
	'id_member' => $user_id
);
$sql = 'UPDATE alumno SET' . $db->sql_build('UPDATE', $update_alumno) . $db->__prepare('
	WHERE id_alumno = ?', $student_id);
$db->sql_query($sql);

// 
// Create user login for supervisor
// 
if (trim($encargado)) {
	$supervisor_base = simple_alias($encargado);
	$supervisor_password = substr(md5(unique_id()), 0, 8);

	$sql = 'SELECT user_id
		FROM _members
		WHERE username_base = ?';
	if (!$supervisor_id = sql_field(sql_filter($sql, $supervisor_base), 'user_id', 0)) {
		$supervisor_data = array(
			'user_type' => USER_NORMAL,
			'user_active' => 1,
			'username' => $encargado,
			'username_base' => $supervisor_base,
			'user_password' => HashPassword($supervisor_password),
			'user_regip' => $user->ip,
			'user_session_time' => 0,
			'user_lastpage' => '',
			'user_lastvisit' => time(),
			'user_regdate' => time(),
			'user_level' => 0,
			'user_posts' => 0,
			'userpage_posts' => 0,
			'user_points' => 0,
			'user_timezone' => $config->board_timezone,
			'user_dst' => $config->board_dst,
			'user_lang' => $config->default_lang,
			'user_dateformat' => $config->default_dateformat,
			'user_country' => $country,
			'user_rank' => 0,
			'user_avatar' => '',
			'user_avatar_type' => 0,
			'user_email' => $encargado_email,
			'user_lastlogon' => 0,
			'user_totaltime' => 0,
			'user_totallogon' => 0,
			'user_totalpages' => 0,
			'user_gender' => 1,
			'user_birthday' => $birthdate,
			'user_upw' => $supervisor_password,
			'user_mark_items' => 0,
			'user_topic_order' => 0,
			'user_email_dc' => 1,
			'user_refop' => 0,
			'user_refby' => ''
		);
		$supervisor_id = sql_insert('members', $supervisor_data);
	}

	$supervisor_student = array(
		'supervisor' => $supervisor_id,
		'student' => $user_id
	);
	$rel_id = sql_create('alumnos_encargados', $supervisor_student);
}

redirect('/adm/alumnos/index2.php');