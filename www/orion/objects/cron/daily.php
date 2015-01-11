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
if (!defined('IN_APP')) exit;

$d = getdate();
$start_1 = mktime(0, 0, 0, $d['mon'], ($d['mday'] - 7), $d['year']);
$start_2 = mktime(0, 0, 0, $d['mon'], ($d['mday'] - 14), $d['year']);

//
// Optimize
set_config('site_disable', 1);

$sql = 'SHOW TABLES';
$result = sql_rowset($sql, false, false, false, MYSQL_NUM);

foreach ($result as $row) {
	$tables[] = $row[0];
}

$sql = 'OPTIMIZE TABLE ' . implode(', ', $tables);
sql_query($sql);

set_config('site_disable', 0);

_pre('Done.', true);
