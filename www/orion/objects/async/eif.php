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

$event_id = request_var('event_id', 0);
$image_id = request_var('image_id', 0);

$sql = 'SELECT *
	FROM _events_images
	WHERE event_id = ?
		AND image = ?';
if (!$imaged = sql_fieldrow(sql_filter($sql, $event_id, $image_id))) {
	fatal_error();
}
$image_footer = request_var('image_footer', '', true);

$sql = 'UPDATE _events_images SET image_footer = ?
	WHERE event_id = ?
		AND image = ?';
sql_query(sql_filter($sql, $image_footer, $event_id, $image_id));

echo $image_footer;
exit;
