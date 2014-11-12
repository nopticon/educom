<?php
/*
<Orion, a web development framework for RK.>
Copyright (C) <2011>  <Orion>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
define('IN_APP', true);
require_once('./interfase/common.php');

$user->init();
$user->setup();

$sql = 'SELECT *
	FROM _radio
	ORDER BY show_day, show_start';
$result = sql_rowset($sql);

$radio = array();
foreach ($result as $row) {
	$row['show_start'] = mktime(($row['show_start'] + $user->data['user_timezone'] + $user->data['user_dst']), 0, 0, 0, 0, 0);
	$row['show_end'] = mktime(($row['show_end'] + $user->data['user_timezone'] + $user->data['user_dst']), 0, 0, 0, 0, 0);
	
	$row['show_start'] = date('G', $row['show_start']);
	$row['show_end'] = date('G', $row['show_end']);
	
	if ((int) $row['show_end'] === 0) {
		$row['show_end'] = 24;
	}
	
	$radio[$row['show_day']][] = $row;
}

$days = array(1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday');
$hours = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24);

foreach ($radio as $d => $row_day) {
	_style('day', array(
		'V_NAME' => $user->lang['datetime'][$days[$d]])
	);
	
	foreach ($hours as $h) {
		
	}
	
	foreach ($row_day as $d2 => $row_show) {
		_style('day.row', array(
			'V_NAME' => $row_show['show_name'])
		);
	}
}

page_layout('RADIO_INDEX', 'radio_body');