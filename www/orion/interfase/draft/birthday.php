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

$cm = (int) date('m');

$sql = 'SELECT username, user_birthday
	FROM _members
	ORDER BY username_base';
$members = sql_rowset($sql);

$a = $b = $u = w();
$i = 0;

foreach ($members as $row) {
	$p = array(
		(int) substr($row['user_birthday'], 4, 2),
		(int) substr($row['user_birthday'], 6, 2),
		(int) substr($row['user_birthday'], 0, 4)
	);
	
	if ($cm != $p[0]) {
		continue;
	}
	
	$u[$row['username']] = $p;
}

foreach ($u as $n => $d) {
	$a[$i] = $d[1];
	$b[$i] = $n;
	$i++;
}

asort($a);
foreach ($a as $i => $d) {
	echo $d . ' > :i' . $b[$i] . ':<br />';
}