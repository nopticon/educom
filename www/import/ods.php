<?php

require 'vendor/autoload.php';

require_once('../adm/conexion.php');

function remove_spaces($str) {
	return str_replace(' ', '', $str);
}

function pr($mixed) {
	echo '<pre>';
	var_dump($mixed);
	exit;
}

function dd($mixed) {
	echo '<pre>';
	print_r($mixed);
	exit;
}

$filepath = realpath(__DIR__ . '/../../private/list.ods');

if (!@file_exists($filepath)) {
	echo 'File not found'; exit;
}

// 
// Init vars
// 
$j 			= 0;
$country 	= 90;
$build 		= [];
$list 		= [];
$grade 		= [];
$section 	= [];
$forums 	= [];
$all 		= [];
$header 	= false;

$student_status = 'Inscrito';
$student_year 	= date('Y');

$gender_select = [
	'M' => 1,
	'F' => 2
];

// 
// Read ODS file
// 
$inputFileType = PHPExcel_IOFactory::identify($filepath);
$objReader = PHPExcel_IOFactory::createReader($inputFileType);
$objPHPExcel = $objReader->load($filepath);
$worksheet = $objPHPExcel->getActiveSheet();

foreach ($worksheet->getRowIterator() as $row) {
	$cellIterator = $row->getCellIterator();
	$cellIterator->setIterateOnlyExistingCells(false);

	foreach ($cellIterator as $cell) {
		$cell_value = $cell->getValue();
		$cell_value = htmlentities($cell_value);

		$list[$cell->getRow()][$cell->getColumn()] = $cell_value;
	}
}

// 
// Parse all information about students
// 
foreach ($list as $i => $row) {
	if ($header === false) {
		$header = $row;
		$header['A'] = 'No';
		$header = array_map('strtolower', $header);
		$header = array_map('remove_spaces', $header);

		continue;
	}

	$all_empty = true;
	foreach ($row as $col) {
		if (!empty($col)) {
			$all_empty = false;
		}
	}

	if ($all_empty) {
		continue;
	}

	foreach ($row as $col => $val) {
		$build[$j][$header[$col]] = trim($val);
		unset($list[$i][$col]);
	}

	$j++;
}

// 
// Get current school forum category
// 
$sql = 'SELECT cat_id
	FROM _forum_categories
	WHERE cat_title = ?';
$school_category = sql_field(sql_filter($sql, 'Colegio San Gabriel'), 'cat_id', 0);

// 
// Truncate tables
// 
sql_query(sql_filter('TRUNCATE TABLE ??', 'secciones'));
sql_query(sql_filter('TRUNCATE TABLE ??', 'grado'));
sql_query(sql_filter('TRUNCATE TABLE ??', '_forums'));
sql_query(sql_filter('TRUNCATE TABLE ??', 'reinscripcion'));

// 
// Parse and create grades and sections
// 
$grade_order = 1;
$forum_order = 1;

foreach ($build as $i => $row) {
	foreach ($row as $col => $value) {
		switch ($col) {
			case 'grade':
				$split_row = explode(' ', $value);
				$section_str = 'A';

				if (count($split_row) > 1) {
					$last = array_pop($split_row);

					if (strlen($last) == 1) {
						$section_str = $last;
						$row['grade'] = implode(' ', $split_row);
					}
				}

				$row['section'] = $section_str;
				break;
			default:
				break;
		}
	}

	$build[$i] = $row;

	if (!isset($grade[$row['grade']])) {
		$sql_insert_grade = [
			'fecha_grado' => '',
			'nombre' => $row['grade'],
			'seccion' => '',
			'status' => 'Alta',
			'grade_order' => $grade_order
		];
		$grade_id = sql_create('grado', $sql_insert_grade);

		$grade[$row['grade']] = $grade_id;
		$grade_order++;
	}

	$grade_section = $row['grade'] . ' ' . $row['section'];

	if (!isset($section[$grade_section])) {
		$sql_insert_section = [
			'id_grado' => $grade[$row['grade']],
			'nombre_seccion' => $row['section']
		];
		$section_id = sql_create('secciones', $sql_insert_section);

		$section[$grade_section] = $section_id;
	}

	if (!isset($forums[$grade_section])) {
		$grade_section_words = ucwords(strtolower($grade_section));

		$insert_forum = array(
			'cat_id' => $school_category,
			'forum_active' => 1,
			'forum_alias' => friendly($grade_section),
			'forum_name' => $grade_section_words,
			'forum_desc' => 'Foro para alumnos de ' . $grade_section_words,
			'forum_locked' => 0,
			'forum_order' => ($forum_order * 10),
			'forum_posts' => 0,
			'forum_topics' => 0,
			'forum_last_topic_id' => 0,
			'auth_view' => 1,
			'auth_read' => 1,
			'auth_post' => 1,
			'auth_reply' => 0,
			'auth_announce' => 3,
			'auth_vote' => 1,
			'auth_pollcreate' => 1
		);
		$forum_id = sql_create('_forums', $insert_forum);
		$forum_order++;

		$forums[$grade_section] = $forum_id;
	}
}

// 
// Compile all students
// 
foreach ($build as $i => $row) {
	$user_full = $row['firstname'] . ' ' . $row['lastname'];
	$user_base = simple_alias($user_full);
	$user_password = substr(md5(unique_id()), 0, 8);
	$user_gender = isset($gender_select[$row['gender']]) ? $gender_select[$row['gender']] : 1;
	$user_grade = $row['grade'] . ' ' . $row['section'];

	$compile = (object) [
		'gender' => $user_gender,
		'firstname' => $row['firstname'],
		'lastname' => $row['lastname'],
		'fullname' => $user_full,
		'base' => $user_base,
		'password' => $user_password,
		'code' => (int) $row['code'],

		'grade' => $grade[$row['grade']],
		'section' => $section[$user_grade],
		'grade_full' => $user_grade
	];

	$insert_member = array(
		'user_type' => USER_NORMAL,
		'user_active' => 1,
		'username' => $compile->fullname,
		'username_base' => $compile->base,
		'user_password' => HashPassword($user_password),
		'user_regip' => $_SERVER['REMOTE_ADDR'],
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
		'user_email' => '',
		'user_lastlogon' => 0,
		'user_totaltime' => 0,
		'user_totallogon' => 0,
		'user_totalpages' => 0,
		'user_gender' => $compile->gender,
		'user_birthday' => '',
		'user_upw' => $user_password,
		'user_mark_items' => 0,
		'user_topic_order' => 0,
		'user_email_dc' => 1,
		'user_refop' => 0,
		'user_refby' => ''
	);
	$member_id = sql_create('_members', $insert_member);

	$insert_student = array(
		'id_member' => $member_id,
		'carne' => $compile->code,
		'codigo_alumno' => '',
		'nombre_alumno' => $compile->firstname,
		'apellido' => $compile->lastname,
		'direccion' => '',
		'orden' => '',
		'registro' => '',
		'telefono1' => '',
		'edad' => '',
		'sexo' => $compile->gender,
		'email' => '',
		'padre' => '',
		'madre' => '',
		'encargado' => '',
		'profesion' => '',
		'labora' => '',
		'direccion_labora' => '',
		'email_encargado' => '',
		'dpi' => '',
		'extendida' => '',
		'emergencia' => '',
		'telefono2' => '',
		'status' => $student_status
	);
	$student_id = sql_create('alumno', $insert_student);

	$insert_subscribe = array(
		'id_alumno' => $student_id,
		'carne' => $compile->code,
		'id_grado' => $compile->grade,
		'id_seccion' => $compile->section,
		'encargado_reinscripcion' => '',
		'telefonos' => '',
		'status' => $student_status,
		'anio' => $student_year
	);
	$reinscription_id = sql_create('reinscripcion', $insert_subscribe);

	$all[] = $compile;
}

dd($all);