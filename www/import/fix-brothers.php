<?php

define('NO_LOGIN', true);

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

function dd($mixed, $halt = true) {
	echo '<pre>';
	print_r($mixed);
	
	if ($halt) exit;
}

$filepath = realpath(__DIR__ . '/../../private/jul21/fix-brothers.xls');

if (!@file_exists($filepath)) {
	echo 'File not found'; exit;
}

// 
// Init vars
// 
$sheets = array(
	0 => 'alumnos'
);

$j 			= 0;
$country 	= 90;
$build 		= [];
$list 		= [];
$grade 		= [];
$section 	= [];
$all 		= [];

// 
// Read ODS file
// 
$inputFileType = PHPExcel_IOFactory::identify($filepath);
$objReader = PHPExcel_IOFactory::createReader($inputFileType);
$objPHPExcel = $objReader->load($filepath);

foreach ($sheets as $id => $title) {
	$objPHPExcel->setActiveSheetIndex($id);
	$worksheet = $objPHPExcel->getActiveSheet();

	foreach ($worksheet->getRowIterator() as $row) {
		$cellIterator = $row->getCellIterator();
		$cellIterator->setIterateOnlyExistingCells(true);

		foreach ($cellIterator as $cell) {
			// $cell_value = $cell->getValue();
			$cell_value = $cell->getCalculatedValue();
			$cell_value = htmlentities($cell_value);

			// $list[$title][$cell->getRow()][$cell->getColumn()] = $cell_value;
			$list[$title][$cell->getRow()][] = $cell_value;
		}
	}
}

foreach ($list as $title => $data) {
	$header = false;
	$j = 0;

	foreach ($data as $i => $row) {
		if ($header === false) {
			$header = $row;

			$header = array_map('strtolower', $header);
			$header = array_map('remove_spaces', $header);
			$header = array_map('alias', $header);

			continue;
		}

		foreach ($row as $col => $val) {
			$build[$title][$j][$header[$col]] = trim($val);
			unset($list[$i][$col]);
		}

		$j++;
	}
}

$assoc = $update_supervisor = $skip_supervisor = [];

foreach ($build['alumnos'] as $i => $row) {
	$assoc[$row['encargado']][] = $row;
}

foreach ($assoc as $name => $row) {
	if (count($row) < 2) unset($assoc[$name]);
}

foreach ($assoc as $assoc_name => $assoc_list) {
	foreach ($assoc_list as $i => $row) {
		$student_alias = simple_alias($row['alumno']);
		$supervisor_alias = simple_alias($row['encargado']);

		$sql = 'SELECT user_id
			FROM _members
			WHERE username_base = ?';
		$student_id = (int) sql_field(sql_filter($sql, $student_alias), 'user_id', 0);

		$sql = 'SELECT user_id
			FROM _members
			WHERE username_base = ?';
		$supervisor_id = (int) sql_field(sql_filter($sql, $supervisor_alias), 'user_id', 0);

		$sql = 'SELECT supervisor
			FROM alumnos_encargados
			WHERE student = ?';
		$old_rel_id = (int) sql_field(sql_filter($sql, $student_id), 'supervisor', 0);

		$assoc[$assoc_name][$i]['user_id'] = $student_id;
		$assoc[$assoc_name][$i]['supervisor_id'] = $supervisor_id;
		$assoc[$assoc_name][$i]['supervisor_old'] = $old_rel_id;

		if ($student_id) {
			if ($supervisor_id != $old_rel_id) {
				$sql = 'UPDATE alumnos_encargados SET supervisor = ? WHERE supervisor = ? AND student = ?;';
				$sql = sql_filter($sql, $supervisor_id, $old_rel_id, $student_id);

				$update_supervisor[] = $sql;

				$sql = 'DELETE FROM _members WHERE user_id = ?;';
				$sql = sql_filter($sql, $old_rel_id);

				$update_supervisor[] = $sql;

				$sql = 'DELETE FROM alumnos_encargados WHERE supervisor = ?;';
				$sql = sql_filter($sql, $old_rel_id);

				$update_supervisor[] = $sql;
			}
		} else {
			$skip_supervisor[] = [
				'student' => $student_alias,
				'supervisor' => $supervisor_alias,
				'supervisor_id' => $supervisor_id,
			];
		}

		// sql_query($sql);
	}
}

echo '<pre>';
echo implode('<br />', $update_supervisor);
exit;

dd($skip_supervisor, false);
dd($update_supervisor);
dd($assoc);
exit;

foreach ($build['alumnos'] as $i => $row) {
	$sql = 'SELECT *
		FROM _members
		WHERE user_upw = ?';
	$sql_row = sql_fieldrow(sql_filter($sql, $row['password']));

	// 
	// Update student member id
	// 
	$sql_update = array(
		'username' => $row['firstname'],
		'username_base' => simple_alias($row['firstname'])
	);
	$sql = 'UPDATE _members SET' . sql_build('UPDATE', $sql_update) . ' WHERE user_id = ' . $sql_row->user_id;
	sql_query($sql);

	$supervisor_name = 'Encargado ' . $row['no'];

	// 
	// Update student table
	// 
	$sql_update = array(
		'carne' => $row['no'],
		'nombre_alumno' => $row['firstname'],
		'encargado' => $supervisor_name
	);
	$sql = 'UPDATE alumno SET' . sql_build('UPDATE', $sql_update) . ' WHERE id_member = ' . $sql_row->user_id;
	sql_query($sql);

	// 
	// Get student supervisor
	// 
	$sql = 'SELECT *
		FROM alumnos_encargados
		WHERE student = ?';
	$sql_supervisor = sql_fieldrow(sql_filter($sql, $sql_row->user_id));

	// 
	// Update supervisor member
	// 

	$sql_update = array(
		'username' => $supervisor_name,
		'username_base' => simple_alias($supervisor_name)
	);
	$sql = 'UPDATE _members SET' . sql_build('UPDATE', $sql_update) . ' WHERE user_id = ' . $sql_supervisor->supervisor;
	sql_query($sql);

	$all[] = $row['firstname'];
}

dd($all);
