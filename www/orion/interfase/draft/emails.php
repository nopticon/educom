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

$forum_id = 22;

$sql = 'SELECT *
	FROM _forum_topics
	WHERE forum_id = ' . (int) $forum_id;
$result = sql_rowset(sql_filter($sql, $forum_id));

$a_topics = w();
foreach ($result as $row) {
	$topic_id = $row['topic_id'];
	
	echo '<strong>' . $row['topic_title'] . '</strong><br /><blockquote>';
	
	$sql = 'SELECT vd.vote_id, vd.vote_text, vd.vote_start, vd.vote_length, vr.vote_option_id, vr.vote_option_text, vr.vote_result
		FROM _poll_options vd, _poll_results vr
		WHERE vd.topic_id = ?
			AND vr.vote_id = vd.vote_id
		ORDER BY vr.vote_option_order, vr.vote_option_id ASC';
	$result2 = sql_rowset(sql_filter($sql, $topic_id));
	
	foreach ($result2 as $row) {
		$subdomain = get_username_base($row['vote_option_text']);
		
		echo '<h1>' . ucwords($subdomain) . '</h1><br /><blockquote>';
		
		$sql = 'SELECT *
			FROM _artists
			WHERE subdomain = ?';
		$row3 = sql_fieldrow(sql_filter($sql, $subdomain));
		
		$sql = 'SELECT m.username, m.user_email
			FROM _artists_auth a, _members m
			WHERE a.ub = ' . (int) $row3['ub'] . '
				AND a.user_id = m.user_id
			ORDER BY username';
		$result4 = sql_rowset(sql_filter($sql, $row3['ub']));
		
		$ii = 0;
		foreach ($result4 as $row4) {
			echo (($ii) ? ', ' : '') . $row4['username'] . ' &lt;' . $row4['user_email'] . '&gt;';
			$ii++;
		}
		
		echo '</blockquote>';
	}
	
	echo '</blockquote>';
}

_pre('', true);