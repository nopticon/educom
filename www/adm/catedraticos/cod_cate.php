<?php

require_once('../conexion.php');

$nombre = request_var('nombre', '');
$apellido = request_var('apellido', '');
$profesion = request_var('profesion', '');
$email = request_var('email', '');
$telefonos = request_var('telefonos', '');
$direccion = request_var('direccion', '');
$observacion = request_var('observacion', '');
$gender = request_var('gender', 0);

$registro = 'cmemou';
$status = 'Alta';

$country = 90;
$birthdate = '';

$full_name = $nombre . ' ' . $apellido;
$username_base = get_username_base($full_name);

if (!$nombre || !$apellido) {
	redirect('/adm/catedraticos/index.php');
}

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

$sql_insert = array(
	'id_member' => $user_id,
	'registro' => $registro,
	'nombre_catedratico' => $nombre,
	'apellido' => $apellido,
	'profesion' => $profesion,
	'email' => $email,
	'telefono' => $telefonos,
	'direccion' => $direccion,
	'observacion' => $observacion,
	'status' => $status
);
$sql = 'INSERT INTO catedratico' . $db->sql_build('INSERT', $sql_insert);
$db->sql_query($sql);

$cache->delete('team_teacher');

redirect('/adm/catedraticos/index.php');