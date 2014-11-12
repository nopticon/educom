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

/*
U			USERS									ADM
A			ARTISTS								-
E			EVENTS								-
N			NEWS									-
P			POSTS									-
D			DOWNLOADS							-
C			ARTISTS MESSAGES			ADM / USER_MOD / USER_FAN
M			DOWNLOADS MESSAGES		ADM / USER_MOD / USER_FAN
W			WALLPAPERS / ART			-
F			ARTISTS NEWS					-
I			ARTISTS IMAGES				-
*/

define('IN_APP', true);
require_once('./interfase/common.php');
require_once(ROOT . 'interfase/downloads.php');

$user->init();

if (!$user->is('member')) {
	do_login();
}

$unread_element = request_var('elem', 0);
$unread_item = request_var('item', 0);

if (isset($_POST['items']) && (isset($_POST['delete']) || isset($_POST['delete_all']))) {
	$items = (is_array($_POST['items']) && !empty($_POST['items'])) ? $_POST['items'] : w();
	
	if (isset($_POST['delete_all'])) {
		foreach ($items as $element => $data) {
			// TODO: Today save
			// $user->delete_unread($element, $data);
		}
	} else {
		foreach ($items as $element => $void) {
			if (isset($_POST['delete'][$element])) {
				// TODO: Today save
				// $user->delete_unread($element, $items[$element]);
				break;
			}
		}
	}
	
	redirect(s_link('today'));
} else if (isset($_POST['options'])) {
	$mark_option = (isset($_POST['mark_read_option'])) ? $_POST['mark_read_option'] : $user->data['user_mark_items'];
	$mark_option = intval($mark_option);
	
	if ($user->data['user_mark_items'] != $mark_option) {
		$sql = 'UPDATE _members SET user_mark_items = ?
			WHERE user_id = ?';
		sql_query(sql_filter($sql, $mark_option, $user->data['user_id']));
	}
	
	redirect(s_link('today'));
} else if ($unread_element && $unread_item) {
	$url = '';
	$delete_item = TRUE;
	
	if ($url != '') {
		if ($user->data['user_mark_items'] && $delete_item) {
			// TODO: Today save
			// $user->delete_unread($unread_element, $unread_item);
		}
		
		redirect($url);
	}
	
	redirect(s_link('today'));
	
}

$user->setup();

//
// Show unread list
//
$sql = 'SELECT element
	FROM _members_unread
	WHERE user_id = ?
	ORDER BY element, item';
if ($result = sql_rowset(sql_filter($sql, $user->data->user_id))) {
	
	$items = w();
	foreach ($result as $row) {
		if (!isset($items[$row->element])) {
			$items[$row->element] = 0;
		}
		$items[$row->element]++;
	}
	
	if (isset($items[UH_D]) || isset($items[UH_M])) {
		$downloads = new downloads();
	}
	
	_style('items', array(
		'TOTAL_ITEMS' => count($result))
	);
	
	//
	// Notes (PM)
	//			
	if (isset($items[UH_NOTE]))
	{
		$sql = 'SELECT c.*, c2.privmsgs_date, m.user_id, m.username, m.username_base
			FROM _members_unread u, _dc c, _dc c2, _members m
			WHERE u.user_id = ?
				AND u.element = ?
				AND u.item = c.msg_id 
				AND c.last_msg_id = c2.msg_id
				AND c2.privmsgs_from_userid = m.user_id 
			ORDER BY c2.privmsgs_date DESC';
		$result = sql_rowset(sql_filter($sql, $user->data->user_id, UH_NOTE));
		
		foreach ($result as $i => $row) {
			if (!$i) {
				_style('items.notes', array(
					'ELEMENT' => UH_NOTE)
				);
			}
			
			$user_profile = $comments->user_profile($row);
			$dc_subject = 'Conversaci&oacute;n con ' . $row->username;
			
			_style('items.notes.item', array(
				'S_MARK_ID' => $row->parent_id,
				'U_READ' => s_link('my dc read', $row->last_msg_id) . '#' . $row->last_msg_id,
				'SUBJECT' => $dc_subject,
				'DATETIME' => $user->format_date($row->privmsgs_date),
				'USER_ID' => $row->user_id,
				'USERNAME' => $row->username,
				'U_USERNAME' => $user_profile->profile)
			);
		}
	}

	if (isset($items[UH_FRIEND]))
	{
		$sql = 'SELECT u.item, u.datetime, m.user_id, m.username, m.username_base, m.user_rank
			FROM _members_unread u, _members m
			WHERE u.user_id = ?
				AND u.element = ?
				AND u.item = m.user_id
			ORDER BY u.datetime DESC';
		$result = sql_rowset(sql_filter($sql, $user->data['user_id'], UH_FRIEND));
		
		foreach ($result as $i => $row) {
			if (!$i) {
				_style('items.friends', array(
					'ELEMENT' => UH_FRIEND)
				);
			}
			
			$user_profile = $comments->user_profile($row);
			
			_style('items.friends.item', array(
				'S_MARK_ID' => $row['user_id'],
				'U_PROFILE' => s_link('m', $row['username_base']),
				'POST_TIME' => $user->format_date($row['datetime']),
				'USERNAME' => $row['username'])
			);
		}
	}
	
	if (isset($items[UH_UPM]))
	{
		$sql = 'SELECT p.*, u.*, m.user_id, m.username, m.username_base
			FROM _members_unread u, _members_posts p, _members m
			WHERE u.user_id = ?
				AND u.element = ?
				AND u.item = p.post_id
				AND p.poster_id = m.user_id
			ORDER BY p.post_time DESC';
		$result = sql_rowset(sql_filter($sql, $user->data['user_id'], UH_UPM));
		
		foreach ($result as $i => $row) {
			if (!$i) {
				_style('items.userpagem', array(
					'ELEMENT' => UH_UPM)
				);
			}
			
			$user_profile = $comments->user_profile($row);
			
			_style('items.userpagem.item', array(
				'S_MARK_ID' => $row['post_id'],
				'U_PROFILE' => s_link('m', $user->d('username_base')),
				'POST_TIME' => $user->format_date($row['datetime']),
				'USERNAME' => $row['username'])
			);
		}
	}
	
	//
	// Site and Artists News
	//			
	if (isset($items[UH_N]))
	{
		$sql = 'SELECT t.*
			FROM _members_unread u, _forum_topics t
			WHERE u.user_id = ?
				AND u.element = ?
				AND u.item = t.topic_id
			ORDER BY t.topic_time DESC';
		$result = sql_rowset(sql_filter($sql, $user->data['user_id'], UH_N));
		
		foreach ($result as $i => $row) {
			if (!$i) {
				_style('items.a_news', array(
					'ELEMENT' => UH_N)
				);
			}

			//$url = s_link('a', $result);
			
			_style('items.a_news.item', array(
				'S_MARK_ID' => $row['topic_id'],
				'POST_URL' => s_link('topic', $row['topic_id']),
				'POST_TITLE' => $row['topic_title'],
				'POST_TIME' => $user->format_date($row['topic_time']))
			);
		}
	}

	if (isset($items[UH_GN]))
	{
		$sql = 'SELECT n.*
			FROM _members_unread u, _news n
			WHERE u.user_id = ?
				AND u.element = ?
				AND u.item = n.news_id
			ORDER BY n.post_time DESC';
		$result = sql_rowset(sql_filter($sql, $user->data['user_id'], UH_GN));
		
		foreach ($result as $i => $row) {
			if (!$i) {
				_style('items.news', array(
					'ELEMENT' => UH_GN)
				);
			}
			
			_style('items.news.item', array(
				'S_MARK_ID' => $row['news_id'],
				
				'POST_URL' => s_link('news', $row['news_alias']),
				'POST_TITLE' => $row['post_subject'],
				'POST_TIME' => $user->format_date($row['post_time']))
			);
		}
	}
	
	//
	// Artists
	//
	if (isset($items[UH_A]))
	{
		$sql = 'SELECT a.ub, a.name, a.datetime 
			FROM _members_unread u, _artists a 
			WHERE u.user_id = ? 
				AND u.element = ? 
				AND u.item = a.ub 
			ORDER BY name';
		$result = sql_rowset(sql_filter($sql, $user->data['user_id'], UH_A));
		
		foreach ($result as $i => $row) {
			if (!$i) {
				_style('items.artists', array(
					'ELEMENT' => UH_A)
				);
			}
			
			_style('items.artists.item', array(
				'S_MARK_ID' => $row['ub'],
				'UB_URL' => s_link('a', $row['subdomain']),
				'NAME' => $row['name'],
				'POST_TIME' => $user->format_date($row['datetime']))
			);
		}
	}
	
	//
	// Downloads
	//
	if (isset($items[UH_D]))
	{
		$sql = 'SELECT b.ub, b.subdomain, b.name, d.id, d.ud AS ud_type, d.title, d.date 
			FROM _members_unread u, _artists b, _dl d 
			WHERE u.user_id = ? 
				AND u.element = ?
				AND u.item = d.id 
				AND d.ub = b.ub 
			ORDER BY d.id DESC';
		$result = sql_rowset(sql_filter($sql, $user->data['user_id'], UH_D));
		
		foreach ($result as $i => $row) {
			if (!$i) {
				_style('items.downloads', array(
					'ELEMENT' => UH_D)
				);
			}
			
			_style('items.downloads.item', array(
				'S_MARK_ID' => $row['id'],
				'UB_URL' => s_link('a', $row['subdomain']),
				'UD_URL' => s_link('a', $row['subdomain'], 9, $row['id']),
				'UD_TYPE' => array_key($downloads->media_type($row['ud_type']), 'av'),
				'DATETIME' => $user->format_date($row['date']),
				'UB' => $row['name'],
				'UD' => $row['title'])
			);
		}
	}
	
	//
	// Forum Topics
	//
	if (isset($items[UH_T]))
	{
		$sql = 'SELECT t.*, f.forum_alias, f.forum_id, f.forum_name, p.post_id, p.post_username, p.post_time, m.user_id, m.username, m.username_base 
			FROM _members_unread u, _forums f, _forum_topics t, _forum_posts p, _members m 
			WHERE u.user_id = ? 
				AND f.forum_id NOT IN (??)
				AND u.element = ? 
				AND u.item = t.topic_id 
				AND t.topic_id = p.topic_id 
				AND t.topic_last_post_id = p.post_id 
				AND t.forum_id = f.forum_id 
				AND p.poster_id = m.user_id 
			ORDER BY t.topic_announce DESC, p.post_time DESC';
		$result = sql_rowset(sql_filter($sql, $user->data['user_id'], '22' . forum_for_team_not(), UH_T));
		
		foreach ($result as $i => $row) {
			if (!$i) {
				_style('items.forums', array(
					'ELEMENT' => UH_T)
				);
			}
			
			$user_profile = $comments->user_profile($row);
			
			_style('items.forums.item', array(
				'S_MARK_ID' => $row['topic_id'],
				'FOR_MODS' => in_array($row['forum_id'], forum_for_team_array()),
				'TOPIC_URL' => s_link('post', $row['post_id']) . '#' . $row['post_id'],
				'TOPIC_TITLE' => $row['topic_title'],
				'TOPIC_REPLIES' => $row['topic_replies'],
				'TOPIC_COLOR' => $row['topic_color'],
				'FORUM_URL' => s_link('forum', $row['forum_alias']),
				'FORUM_NAME' => $row['forum_name'],
				'DATETIME' => $user->format_date($row['post_time']),
				'USER_ID' => $row['user_id'],
				'USER_PROFILE' => $user_profile['profile'],
				'USERNAME' => $user_profile['username'])
			);
		}
	}
	
	//
	// Artists comments
	//
	if (isset($items[UH_C]))
	{
		$sql = 'SELECT b.subdomain, b.name, p.*, m.user_id, m.username, m.username_base 
			FROM _members_unread u, _artists b, _artists_posts p, _members m 
			WHERE u.user_id = ? 
				AND u.element = ? 
				AND u.item = p.post_id
				AND b.ub = p.post_ub 
				AND p.poster_id = m.user_id 
				AND p.post_active = 1 
			ORDER BY p.post_id DESC';
		$result = sql_rowset(sql_filter($sql, $user->data['user_id'], UH_C));
		
		foreach ($result as $i => $row) {
			if (!$i) {
				_style('items.a_messages', array(
					'ELEMENT' => UH_C)
				);
			}
			
			$user_profile = $comments->user_profile($row);

			_style('items.a_messages.item', array(
				'S_MARK_ID' => $row['post_id'],
				'ITEM_URL' => s_link('a', $row['subdomain'], 12, $row['post_id']),
				'UB_URL' => s_link('a', $row['subdomain']),
				'UB' => $row['name'],
				'DATETIME' => $user->format_date($row['post_time']),
				'USER_ID' => $row['user_id'],
				'USER_PROFILE' => $user_profile['profile'],
				'USERNAME' => $user_profile['username'])
			);
		}
	}
	
	//
	// Downloads comments
	//
	if (isset($items[UH_M]))
	{
		$sql = "SELECT b.ub, b.subdomain, b.name, d.id AS dl_id, d.ud AS ud_type, d.title, m.*, u.user_id, u.username, u.username_base
			FROM _members_unread ur, _artists b, _dl d, _dl_posts m, _members u 
			WHERE ur.user_id = " . $user->data['user_id'] . " 
				AND ur.element = " . UH_M . " 
				AND ur.item = m.post_id 
				AND m.download_id = d.id 
				AND d.ub = b.ub 
				AND m.poster_id = u.user_id 
				AND m.post_active = 1 
			ORDER BY m.post_id DESC";
		$result = sql_rowset(sql_filter($sql, $user->data['user_id'], UH_M));
		
		foreach ($result as $i => $row) {
			if (!$i) {
				_style('items.d_messages', array(
					'ELEMENT' => UH_M)
				);
			}
			
			$download_type = $downloads->media_type($row['ud_type']);
			$user_profile = $comments->user_profile($row);
			
			_style('items.d_messages.item', array(
				'S_MARK_ID' => $row['post_id'],
				'ITEM_URL' => s_link('a', $row['subdomain'], 9, $row['dl_id']),
				'UB_URL' => s_link('a', $row['subdomain']),
				'UD_URL' => s_link('a', $row['subdomain'], 9, $row['dl_id']),
				'UD_TYPE' => $download_type['av'],
				'UB' => $row['name'],
				'UD' => $row['title'],
				'POST_TIME' => $user->format_date($row['post_time']),
				'USER_ID' => $row['user_id'],
				'USER_PROFILE' => $user_profile['profile'],
				'USERNAME' => $user_profile['username'])
			);
		}
	}
	
	//
	// Events
	//
	if (isset($items[UH_E])) {
		
	}
	
	//
	// ARTISTS FAV
	//
	if (isset($items[UH_AF]))
	{
		$sql = 'SELECT f.fan_id, f.joined, a.name, a.subdomain, m.user_id, m.username, m.username_base
			FROM _members_unread u, _artists a, _artists_fav f, _members m
			WHERE u.user_id = ?
				AND u.element = ?
				AND u.item = f.fan_id
				AND f.ub = a.ub
				AND f.user_id = m.user_id
			ORDER BY f.joined DESC';
		$result = sql_rowset(sql_filter($sql, $user->data['user_id'], UH_AF));
		
		foreach ($result as $i => $row) {
			if (!$i) {
				_style('items.a_fav', array(
					'ELEMENT' => UH_AF)
				);
			}
			
			$user_profile = $comments->user_profile($row);
			
			_style('items.a_fav.item', array(
				'S_MARK_ID' => $row['fan_id'],
				'ITEM_URL' => s_link('m', $row['username_base']),
				'UB_URL' => s_link('a', $row['subdomain']),
				'UB' => $row['name'],
				'POST_TIME' => $user->format_date($row['joined']),
				'USER_ID' => $row['user_id'],
				'USER_PROFILE' => $user_profile['profile'],
				'USERNAME' => $user_profile['username'])
			);
		}
	}
	
	//
	// Members
	//
	if (isset($items[UH_U]))
	{
		$sql = 'SELECT m.user_id, m.username, m.username_base, m.user_regdate 
			FROM _members_unread u, _members m 
			WHERE u.user_id = ? 
				AND u.element = ? 
				AND u.item = m.user_id 
				AND m.user_active = 1 
			ORDER BY m.user_id DESC';
		$result = sql_rowset(sql_filter($sql, $user->data['user_id'], UH_U));
		
		foreach ($result as $i => $row) {
			if (!$i) {
				_style('items.users', array(
					'ELEMENT' => UH_U)
				);
			}
			
			$user_profile = $comments->user_profile($row);
			
			_style('items.users.item', array(
				'S_MARK_ID' => $row['user_id'],
				'USER_PROFILE' => $user_profile['profile'],
				'USERNAME' => $user_profile['username'],
				'DATETIME' => $user->format_date($row['user_regdate']))
			);
		}
	}
} else {
	_style('no_items');
}

$mark_options = array('NEW_MARK_NEVER', 'NEW_MARK_ALWAYS');
foreach ($mark_options as $i => $mark_item) {
	_style('mark_item', array(
		'ITEM' => $i,
		'NAME' => $user->lang[$mark_item],
		'SELECTED' => (($i == $user->data['user_mark_items']) ? ' selected="selected"' : ''))
	);
}

$sql = 'SELECT d.id, d.title, a.subdomain, a.name
	FROM _dl d, _artists a
	WHERE d.ud = 1
		AND d.ub = a.ub
	ORDER BY d.date DESC
	LIMIT 0, 10';
$result = sql_rowset($sql);

foreach ($result as $row) {
	_style('downloads', array(
		'URL' => s_link('a', $row['subdomain'], 9, $row['id']),
		'A' => $row['name'],
		'T' => $row['title'])
	);
}

v_style(array(
	'S_UNREAD_ACTION' => s_link('today'))
);

//
// Load sidebar
//sidebar('artists', 'events');

page_layout('UNREAD_ITEMS', 'unread_body');