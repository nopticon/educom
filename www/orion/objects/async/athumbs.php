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

// require_once(ROOT . 'interfase/artists.php');

$artists = new artists();

$sql = 'SELECT *
	FROM _artists
	WHERE images > 0
	ORDER BY RAND()
	LIMIT 12';
if (!$selected_artists = sql_rowset($sql, 'ub')) {
	return;
}

$sql = 'SELECT *
	FROM _artists_images
	WHERE ub IN (??)
	ORDER BY RAND()';
$result = sql_rowset(sql_filter($sql, implode(',', array_keys($selected_artists))));

$random_images = w();
foreach ($result as $row) {
	if (!isset($random_images[$row->ub])) {
		$random_images[$row->ub] = $row->image;
	}
}

foreach ($selected_artists as $ub => $data) {
	_style('row', array(
		'NAME' => $data->name,
		'IMAGE' => $config->artists_url . $ub . '/thumbnails/' . $random_images[$ub] . '.jpg',
		'URL' => s_link('a', $data->subdomain),
		'LOCATION' => ($data->local) ? 'Guatemala' : $data->location,
		'GENRE' => $data->genre)
	);
}

$template->set_filenames(array(
	'body' => 'artists.thumbs.htm')
);
$template->pparse('body');

sql_close();
exit;
