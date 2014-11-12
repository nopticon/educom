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

//
// Simple version of jumpbox, just lists authed forums
//
function make_forum_select($box_name, $ignore_forum = false, $select_forum = '') {
	global $userdata;

	$is_auth_ary = auth(AUTH_READ, AUTH_LIST_ALL, $userdata);

	$sql = 'SELECT f.forum_id, f.forum_name
		FROM _forum_categories c, _forums f
		WHERE f.cat_id = c.cat_id 
		ORDER BY c.cat_order, f.forum_order';
	$result = sql_rowset($sql);
	
	$forum_list = '';
	foreach ($result as $row) {
		if ($is_auth_ary[$row['forum_id']]['auth_read'] && $ignore_forum != $row['forum_id']) {
			$selected = ($select_forum == $row['forum_id']) ? ' selected="selected"' : '';
			$forum_list .= '<option value="' . $row['forum_id'] . '"' . $selected .'>' . $row['forum_name'] . '</option>';
		}
	}
	
	$forum_list = ($forum_list == '') ? '<option value="-1">--</option>' : '<select name="' . $box_name . '">' . $forum_list . '</select>';

	return $forum_list;
}

//
// Synchronise functions for forums/topics
//
function sync($type, $id = false) {
	switch($type) {
		case 'all forums':
			$sql = "SELECT forum_id
				FROM _forums";
			$result = sql_rowset($sql);
			
			foreach ($result as $row) {
				sync('forum', $row['forum_id']);
			}
	   	break;
		case 'all topics':
			$sql = 'SELECT topic_id
				FROM _forum_topics';
			$result = sql_rowset($sql);
			
			foreach ($result as $row) {
				sync('topic', $row['topic_id']);
			}
			break;
  	case 'forum':
			$sql = 'SELECT COUNT(post_id) AS total
				FROM _forum_posts
				WHERE forum_id = ?';
			$total_posts = sql_field(sql_filter($sql, $id), 'total', 0);

			//
			$sql = 'SELECT MAX(topic_id) AS last_topic, COUNT(topic_id) AS total
				FROM _forum_topics
				WHERE forum_id = ?';
			if ($row = sql_fieldrow(sql_filter($sql, $id))) {
				$total_topics = ($row['total']) ? $row['total'] : 0;
				$last_topic = ($row['last_topic']) ? $row['last_topic'] : 0;
			}

			$sql = 'UPDATE _forums
				SET forum_last_topic_id = ?, forum_posts = ?, forum_topics = ?
				WHERE forum_id = ?';
			sql_query(sql_filter($sql, $last_topic, $total_posts, $total_topics, $id));
			break;
		case 'topic':
			$sql = 'SELECT MAX(post_id) AS last_post, MIN(post_id) AS first_post, COUNT(post_id) AS total_posts
				FROM _forum_posts
				WHERE topic_id = ?';
			if ($row = sql_fieldrow(sql_filter($sql, $id))) {
				if ($row['total_posts'])
				{
					// Correct the details of this topic
					$sql = 'UPDATE _forum_topics SET topic_replies = ?, topic_first_post_id = ?, topic_last_post_id = ?
						WHERE topic_id = ?';
					sql_query(sql_filter($sql, ($row['total_posts'] - 1), $row['first_post'], $row['last_post'], $id));
				} else {
					// There are no replies to this topic
					// Check if it is a move stub
					$sql = 'SELECT topic_moved_id
						FROM _forum_topics
						WHERE topic_id = ?';
					if ($row = sql_fieldrow(sql_filter($sql, $id))) {
						if (!$row['topic_moved_id']) {
							$sql = 'DELETE FROM _forum_topics WHERE topic_id = ?';
							sql_query(sql_filter($sql, $id));
						}
					}
				}
			}
			break;
	}
	
	return true;
}

function sync_merge($type, $id = false) {
	switch($type) {
		case 'all forums':
			$sql = 'SELECT forum_id
				FROM _forums';
			$result = sql_rowset($sql);
			
			foreach ($result as $row) {
				sync_merge('forum', $row['forum_id']);
			}
			break;
		case 'all topics':
			$sql = 'SELECT topic_id
				FROM _forum_topics';
			$result = sql_rowset($sql);
			
			foreach ($result as $row) {
				sync_merge('topic', $row['topic_id']);
			}
			break;
		case 'forum':
			$sql = 'SELECT COUNT(post_id) AS total
				FROM _forum_posts
				WHERE forum_id = ?';
			$total_posts = sql_field(sql_filter($sql, $id), 'total', 0);
			
			$sql = 'SELECT topic_id
				FROM _forum_posts
				WHERE forum_id = ?
				ORDER BY post_time DESC';
			$last_topic = sql_field(sql_filter($sql, $id), 'topic_id', 0);
			
			$sql = 'SELECT COUNT(topic_id) AS total
				FROM _forum_topics
				WHERE forum_id = ?';
			$total_topics = sql_field(sql_filter($sql, $id), 'total', 0);
			
			$sql = 'UPDATE _forums SET forum_last_topic_id = ?, forum_posts = ?, forum_topics = ?
				WHERE forum_id = ?';
			sql_query(sql_filter($sql, $last_topic, $total_posts, $total_topics, $id));
			break;
		case 'topic':
			$sql = 'SELECT MAX(post_id) AS last_post, MIN(post_id) AS first_post, COUNT(post_id) AS total_posts
				FROM _forum_posts
				WHERE topic_id = ?';
			if ($row = sql_fieldrow(sql_filter($sql, $id))) {
				if ($row['total_posts']) {
					$sql = 'UPDATE _forum_topics SET topic_replies = ?, topic_first_post_id = ?, topic_last_post_id = ?
						WHERE topic_id = ?';
					$sql = sql_filter($sql, ($row['total_posts'] - 1), $row['first_post'], $row['last_post'], $id);
				} else {
					$sql = 'DELETE FROM _forum_topics WHERE topic_id = ?';
					$sql = sql_filter($sql, $id);
				}
				sql_query($sql);
			}
			break;
	}
	
	return true;
}

function sync_move($id) {
	$last_topic = 0;
	$total_posts = 0;
	$total_topics = 0;
	
	//
	$sql = 'SELECT COUNT(post_id) AS total 
		FROM _forum_posts
		WHERE forum_id = ?';
	$total_posts = sql_field(sql_filter($sql, $id), 'total', 0);
	
	$sql = 'SELECT MAX(topic_id) as last_topic, COUNT(topic_id) AS total
		FROM _forum_topics
		WHERE forum_id = ?';
	if ($row = sql_fieldrow(sql_filter($sql, $id))) {
		$last_topic = $row['last_topic'];
		$total_topics = $row['total'];
	}
	
	//
	$sql = 'UPDATE _forums SET forum_last_topic_id = ?, forum_posts = ?, forum_topics = ?
		WHERE forum_id = ?';
	sql_query(sql_filter($sql, $last_topic, $total_posts, $total_topics, $id));
	
	return;
}

function sync_post($id) {
	$last_topic = 0;
	$total_posts = 0;
	$total_topics = 0;
	
	//
	$sql = 'SELECT COUNT(post_id) AS total 
		FROM _forum_posts
		WHERE forum_id = ?';
	$total_posts = sql_field(sql_filter($sql, $id), 'total', 0);
	
	$sql = 'SELECT MAX(topic_id) as last_topic, COUNT(topic_id) AS total
		FROM _forum_topics
		WHERE forum_id = ?';
	if ($row = sql_fieldrow(sql_filter($sql, $id))) {
		$last_topic = $row['last_topic'];
		$total_topics = $row['total'];
	}
	
	//
	$sql = 'UPDATE _forums SET forum_last_topic_id = ?, forum_posts = ?, forum_topics = ?
		WHERE forum_id = ?';
	sql_query(sql_filter($sql, $last_topic, $total_posts, $total_topics, $id));
	
	return;
}

function sync_topic($id) {
	$last_topic = 0;
	$total_posts = 0;
	$total_topics = 0;
	
	//
	$sql = 'SELECT COUNT(post_id) AS total 
		FROM _forum_posts
		WHERE forum_id = ?';
	$total_posts = sql_field(sql_filter($sql, $id), 'total', 0);
	
	$sql = 'SELECT MAX(topic_id) as last_topic, COUNT(topic_id) AS total
		FROM _forum_topics
		WHERE forum_id = ?';
	if ($row = sql_fieldrow(sql_filter($sql, $id))) {
		$last_topic = $row['last_topic'];
		$total_topics = $row['total'];
	}
	
	//
	$sql = 'UPDATE _forums SET forum_last_topic_id = ?, forum_posts = ?, forum_topics = ?
		WHERE forum_id = ?';
	sql_query(sql_filter($sql, $last_topic, $total_posts, $total_topics, $id));
		
	return true;
}

?>