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
define('ROOT', '../../');

require_once(ROOT . 'interfase/common.php');

$user->init();

$sql = 'SELECT ub, genre
	FROM _artists
	ORDER BY ub';
$artists = sql_rowset($sql);

$result = w();
foreach ($artists as $row) {
	if (is_numb($row->genre)) {
		$sql_insert = array(
			'ag_artist' => $row->ub,
			'ag_genre' => $row->genre
		);

		$result[] = sql_insert('artists_genres', $sql_insert);
	}
}

_pre($result);