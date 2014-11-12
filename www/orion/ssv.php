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

$lang = array(
	'SERVER_UPTIME' => 'Server Uptime: %s day(s) %s hour(s) %s minute(s)',
	'SERVER_LOAD' => 'Average Load: %s'
);

$uptime = @exec('uptime');
if (strstr($uptime, 'day')) {
	if (strstr($uptime, 'min')) {
		preg_match("/up\s+(\d+)\s+(days,|days|day,|day)\s+(\d{1,2})\s+min/", $uptime, $times);
		$days = $times[1];
		$hours = 0;
		$mins = $times[3];
	} else {
		preg_match("/up\s+(\d+)\s+(days,|days|day,|day)\s+(\d{1,2}):(\d{1,2}),/", $uptime, $times);
		$days = $times[1];
		$hours = $times[3];
		$mins = $times[4];
	}
} else {
	if (strstr($uptime, 'min')) {
		preg_match("/up\s+(\d{1,2})\s+min/", $uptime, $times);
		$days = 0;
		$hours = 0;
		$mins = $times[1];
	} else {
		preg_match("/up\s+(\d+):(\d+),/", $uptime, $times);
		$days = 0;
		$hours = $times[1];
		$mins = $times[2];
	}
}
preg_match("/averages?: ([0-9\.]+),[\s]+([0-9\.]+),[\s]+([0-9\.]+)/", $uptime, $avgs);
$load = $avgs[1].", ".$avgs[2].", ".$avgs[3]."";

$layout_vars = array(
	'SERVER_UPTIME' => sprintf($lang['SERVER_UPTIME'], $days, $hours, $mins),
	'SERVER_LOAD' => sprintf($lang['SERVER_LOAD'], $load),

	'CLIENT_IP' => $user->ip
);

page_layout('HOME', 'ssv', $layout_vars, false);