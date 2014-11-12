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
// Banners
$banner_end = mktime(23, 59, 0, $d['mon'], $d['mday'], $d['year']);

$sql = 'SELECT *
	FROM _monetize
	WHERE monetize_end > ?
	ORDER BY monetize_end';
$deleted = sql_rowset(sql_filter($sql, $_end), false, 'monetize_id');

if (count($deleted)) {
	$sql = 'DELETE FROM _monetize
		WHERE monetize_id IN (??)';
	sql_query(sql_filter($sql, implode(',', $deleted)));
	
	$cache->delete('monetize');
}

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
