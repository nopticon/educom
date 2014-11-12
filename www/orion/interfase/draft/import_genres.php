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

$genres = @file('genres');
$success = $all = $noexists = $updated = w();

foreach ($genres as $row) {
	$row = trim($row);

	$sql = 'SELECT genre_id
		FROM _genres
		WHERE genre_name = ?';
	if (!$genre_id = sql_field(sql_filter($sql, $row), 'genre_id', 0)) {
		$genre_id = sql_insert('genres', array(
			'genre_alias' => alias($row),
			'genre_name' => $row)
		);

		$success[] = $row;
	}

	$all[strtolower($row)] = $genre_id;
}

$sql = 'SELECT ub, name, genre
	FROM _artists
	ORDER BY ub';
$artists = sql_rowset($sql);

foreach ($artists as $row) {
	if (is_numb($row->genre)) continue;

	$lower = strtolower($row->genre);

	if (!isset($all[$lower])) {
		$noexists[] = $row->name . ' - ' . $row->genre;
		continue;
	}

	$genre_id = $all[$lower];

	$sql = 'UPDATE _artists SET genre = ?
		WHERE ub = ?';
	sql_query(sql_filter($sql, $genre_id, $row->ub));

	$updated[] = $row->name . ' - ' . $row->genre;
}

_pre($success);
_pre($all);

_pre($noexists);
_pre($updated);