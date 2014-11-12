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

$html_entities_match = array('#&(?!(\#\d+;))#', '#<#', '#>#');
$html_entities_replace = array('&amp;', '&lt;', '&gt;');

$unhtml_specialchars_match = array('#&gt;#', '#&lt;#', '#&quot;#', '#&amp;#');
$unhtml_specialchars_replace = array('>', '<', '"', '&');

/**
* DECODE TEXT -> This will/should be handled eventually
*/
function decode_message(&$message, $bbcode_uid = '')
{
	global $config;

	$message = str_replace('<br />', nr(), $message);

	$match = array(
		'#<!\-\- e \-\-><a href="mailto:(.*?)">.*?</a><!\-\- e \-\->#',
		'#<!\-\- m \-\-><a href="(.*?)" target="_blank">.*?</a><!\-\- m \-\->#',
		'#<!\-\- w \-\-><a href="http:\/\/(.*?)" target="_blank">.*?</a><!\-\- w \-\->#',
		'#<!\-\- l \-\-><a href="(.*?)">.*?</a><!\-\- l \-\->#',
		'#<!\-\- s(.*?) \-\-><img src="\{SMILIES_PATH\}\/.*? \/><!\-\- s\1 \-\->#',
		'#<!\-\- h \-\-><(.*?)><!\-\- h \-\->#',
		'#<.*?>#s'
	);
	
	$replace = array('\1', '\1', '\1', '\1', '\1', '&lt;\1&gt;', '');
	
	$message = preg_replace($match, $replace, $message);

	return;
}

//
// This function will prepare a posted message for
// entry into the database.
//
function prepare_message($message)
{
	global $config;
	
	// Do some general 'cleanup' first before processing message,
	// e.g. remove excessive newlines(?), smilies(?)
	// Transform \r\n and \r into \n
	$match = array('#\r\n?#', '#sid=[a-z0-9]*?&amp;?#', "#([\n][\s]+){3,}#", '#(script|about|applet|activex|chrome):#i');
	$replace = array(nr(), '', nr(false, 2), "\\1&#058;");
	$message = preg_replace($match, $replace, trim($message));
	
	$allowed_tags = split(',', $config['allow_html_tags']);
	
	if (sizeof($allowed_tags))
	{
		$message = preg_replace('#&lt;(\/?)(' . str_replace('*', '.*?', implode('|', $allowed_tags)) . ')&gt;#is', '<$1$2>', $message);
	}
	
	return $message;
}

function unprepare_message($message)
{
	global $unhtml_specialchars_match, $unhtml_specialchars_replace;

	return preg_replace($unhtml_specialchars_match, $unhtml_specialchars_replace, $message);
}

//
// Prepare a message for posting
// 
function prepare_post(&$mode, &$post_data, &$bbcode_on, &$html_on, &$smilies_on, &$error_msg, &$username, &$bbcode_uid, &$subject, &$message, &$nowplaying, &$poll_title, &$poll_options, &$poll_length)
{
	global $config, $userdata, $lang;

	// Check subject
	if (!empty($subject))
	{
		$subject = htmlspecialchars(trim($subject));
	}
	else if ($mode == 'newtopic' || ($mode == 'editpost' && $post_data['first_post']))
	{
		$error_msg .= (!empty($error_msg)) ? '<br />' . $lang['Empty_subject'] : $lang['Empty_subject'];
	}

	// Check message
	if (!empty($message))
	{
		$message = prepare_message($message, $html_on, $bbcode_on, $smilies_on);
	}
	else if ($mode != 'delete' && $mode != 'poll_delete') 
	{
		$error_msg .= (!empty($error_msg)) ? '<br />' . $lang['Empty_message'] : $lang['Empty_message'];
	}
	
	//
	// Handle poll stuff
	//
	if ($mode == 'newtopic' || ($mode == 'editpost' && $post_data['first_post']))
	{
		$poll_length = (isset($poll_length)) ? max(0, intval($poll_length)) : 0;

		if (!empty($poll_title))
		{
			$poll_title = htmlspecialchars(trim($poll_title));
		}

		if(!empty($poll_options))
		{
			$temp_option_text = w();
			while(list($option_id, $option_text) = @each($poll_options))
			{
				$option_text = trim($option_text);
				if (!empty($option_text))
				{
					$temp_option_text[$option_id] = htmlspecialchars($option_text);
				}
			}
			$option_text = $temp_option_text;

			if (count($poll_options) < 2)
			{
				$error_msg .= (!empty($error_msg)) ? '<br />' . $lang['To_few_poll_options'] : $lang['To_few_poll_options'];
			}
			else if (count($poll_options) > $config['max_poll_options']) 
			{
				$error_msg .= (!empty($error_msg)) ? '<br />' . $lang['To_many_poll_options'] : $lang['To_many_poll_options'];
			}
			else if ($poll_title == '')
			{
				$error_msg .= (!empty($error_msg)) ? '<br />' . $lang['Empty_poll_title'] : $lang['Empty_poll_title'];
			}
		}
	}

	return;
}

//
// Post a new topic/reply/poll or edit existing post/poll
//
function submit_post($mode, &$post_data, &$message, &$meta, &$forum_id, &$topic_id, &$post_id, &$poll_id, &$topic_type, &$bbcode_on, &$html_on, &$smilies_on, &$attach_sig, &$bbcode_uid, &$post_username, &$post_subject, &$post_message, &$post_np, &$poll_title, &$poll_options, &$poll_length, $ub = '')
{
	global $config, $lang, $userdata, $user_ip, $tree;
	
	$current_time = time();
	
	/*
	//
	// Retreive authentication info to determine if this user has moderator status
	//
	$is_auth = $tree['auth'][POST_FORUM_URL . $forum_id];
	$is_mod = $is_auth['auth_mod'];

	if ($mode == 'newtopic' || $mode == 'reply' && !$is_mod) 
	{
		//
		// Flood control
		//
		$where_sql = ($userdata['user_id'] == GUEST) ? "poster_ip = '$user_ip'" : 'poster_id = ' . $userdata['user_id'];
		$sql = "SELECT MAX(post_time) AS last_post_time
			FROM _forum_posts
			WHERE $where_sql";
		if ($row = sql_fieldrow($result)) {
			if (intval($row['last_post_time']) > 0 && ($current_time - intval($row['last_post_time'])) < intval($config['flood_interval'])) {
				trigger_error('Flood_Error');
			}
		}
	}
	*/
	
	if ($mode == 'newtopic' || ($mode == 'editpost' && $post_data['first_post']))
	{
		$topic_vote = (!empty($poll_title) && count($poll_options) >= 2) ? 1 : 0;
		
		if ($mode != 'editpost') {
			$sql_insert = array(
				'topic_title' => $post_subject,
				'topic_poster' => $userdata['user_id'],
				'topic_time' => $current_time,
				'forum_id' => $forum_id,
				'topic_status' => TOPIC_UNLOCKED,
				'topic_important' => $topic_type,
				'topic_vote' => $topic_vote
			);
			
			if (!empty($ub)) {
				$sql_insert['ub'] = $ub;
			}

			sql_insert('forum_topics', $sql_insert);
		} else {
			$sql_update = array(
				'topic_title' => $post_subject,
				'topic_important' => $topic_type
			);
			
			if ($post_data['edit_vote'] || !empty($poll_title)) {
				$sql_update['topic_vote'] = $topic_vote;
			}
			
			$sql = 'UPDATE _forum_topics SET ??
				WHERE topic_id = ?';
			sql_query(sql_filter($sql, sql_build('UPDATE', $sql_update), $topic_id));
		}
		
		if ($mode == 'newtopic') {
			$topic_id = sql_nextid();
		}
	}

	$edited_sql = ($mode == 'editpost' && !$post_data['last_post'] && $post_data['poster_post']) ? '' : '';
	
	if ($mode != 'editpost') {
		$sql_insert = array(
			'topic_id' => $topic_id,
			'forum_id' => $forum_id,
			'poster_id' => $userdata['user_id'],
			'post_username' => $post_username,
			'post_time' => $current_time,
			'poster_ip' => $user_ip,
			'post_subject' => $post_subject,
			'post_text' => $post_message,
			'post_np' => $post_np
		);
		sql_insert('forum_posts', $sql_insert);
	} else {
		$sql_update = array(
			'post_username' => $post_username,
			'post_subject' => $post_subject,
			'post_text' => $post_text,
			'post_np' => $post_np
		);
		
		$sql = 'UPDATE _forum_posts SET ??
			WHERE post_id = ?';
		sql_query(sql_filter($sql, sql_build('UPDATE', $sql_update), $post_id));
	}
	
	if ($mode != 'editpost') {
		$post_id = sql_nextid();
	}

	//
	// Add poll
	// 
	if (($mode == 'newtopic' || ($mode == 'editpost' && $post_data['edit_poll'])) && !empty($poll_title) && count($poll_options) >= 2)
	{
		if ($post_data['has_poll']) {
			$sql_update = array(
				'vote_text' => $poll_title,
				'vote_length' => ($poll_length * 86400)
			);
			
			$sql = 'UPDATE _poll_options SET ??
				WHERE topic_id = ?';
			sql_query(sql_filter($sql, sql_build('UPDATE', $sql_update), $topic_id));
		} else {
			$sql_insert = array(
				'topic_id' => $topic_id,
				'vote_text' => $poll_title,
				'vote_start' => $current_time,
				'vote_length' => ($poll_length * 86400)
			);
			sql_insert('poll_options', $sql_insert);
		}
		
		$delete_option_sql = '';
		$old_poll_result = w();
		if ($mode == 'editpost' && $post_data['has_poll']) {
			$sql = 'SELECT vote_option_id, vote_result
				FROM _poll_results
				WHERE vote_id = ?
				ORDER BY vote_option_id ASC';
			$result = sql_rowset(sql_filter($sql, $poll_id));
			
			foreach ($result as $row) {
				$old_poll_result[$row['vote_option_id']] = $row['vote_result'];

				if (!isset($poll_options[$row['vote_option_id']])) {
					$delete_option_sql .= ($delete_option_sql != '') ? ', ' . $row['vote_option_id'] : $row['vote_option_id'];
				}
			}
		} else {
			$poll_id = sql_nextid();
		}

		$poll_option_id = 1;
		while (list($option_id, $option_text) = each($poll_options)) {
			if (!empty($option_text)) {
				$option_text = str_replace("\'", "''", htmlspecialchars($option_text));
				$poll_result = ($mode == "editpost" && isset($old_poll_result[$option_id])) ? $old_poll_result[$option_id] : 0;
				
				if ($mode != 'editpost' || !isset($old_poll_result[$option_id])) {
					$sql_insert = array(
						'vote_id' => $poll_id,
						'vote_option_id' => $poll_option_id,
						'vote_option_text' => $option_text,
						'vote_result' => $poll_result
					);
					sql_insert('poll_results', $sql_insert);
				} else {
					$sql_update = array(
						'vote_option_text' => $option_text,
						'vote_result' => $poll_result
					);
					$sql = 'UPDATE _poll_results SET ??
						WHERE vote_option_id = ?
							AND vote_id = ?';
					sql_query(sql_filter($sql, sql_build('UPDATE', $sql_update), $option_id, $poll_id));
				}
				
				$poll_option_id++;
			}
		}

		if (!empty($delete_option_sql))
		{
			$sql = 'DELETE FROM _poll_results
				WHERE vote_option_id IN (??)
					AND vote_id = ?';
			sql_query(sql_filter($sql, $delete_option_sql, $poll_id));
		}
	}
	
	redirect(s_link('post', $post_id) . '#' . $post_id);
	
	return false;
}

//
// Update post stats and details
//
function update_post_stats(&$mode, &$post_data, &$forum_id, &$topic_id, &$post_id, &$user_id)
{
	$sign = ($mode == 'delete') ? '- 1' : '+ 1';
	$forum_update_sql = "forum_posts = forum_posts $sign";
	$topic_update_sql = '';

	if ($mode == 'delete') {
		if ($post_data['last_post']) {
			if ($post_data['first_post']) {
				$forum_update_sql .= ', forum_topics = forum_topics - 1';
			} else {
				$topic_update_sql .= 'topic_replies = topic_replies - 1';

				$sql = 'SELECT MAX(post_id) AS last_post_id
					FROM _forum_posts
					WHERE topic_id = ?';
				if ($last_post_id = sql_field(sql_filter($sql, $topic_id), 'last_post_id', 0)) {
					$topic_update_sql .= sql_filter(', topic_last_post_id = ?', $last_post_id);
				}
			}

			if ($post_data['last_topic']) {
				$sql = 'SELECT MAX(topic_id) AS last_topic_id
					FROM _forum_posts
					WHERE forum_id = ?';
				if ($last_topic_id = sql_field(sql_filter($sql, $forum_id), 'last_topic_id', 0)) {
					$forum_update_sql .= ($last_topic_id) ? ', forum_topic_post_id = ' . $last_topic_id : ', forum_last_topic_id = 0';
				}
			}
		} else if ($post_data['first_post']) {
			$sql = 'SELECT MIN(post_id) AS first_post_id
				FROM _forum_posts
				WHERE topic_id = ?';
			if ($first_post_id = sql_field(sql_filter($sql, $topic_id), 'first_post_id', 0)) {
				$topic_update_sql .= 'topic_replies = topic_replies - 1, topic_first_post_id = ' . $first_post_id;
			}
		} else {
			$topic_update_sql .= 'topic_replies = topic_replies - 1';
		}
	} else if ($mode != 'poll_delete') {
		$forum_update_sql .= ", forum_last_topic_id = $topic_id" . (($mode == 'newtopic') ? ", forum_topics = forum_topics $sign" : ""); 
		$topic_update_sql = "topic_last_post_id = $post_id" . (($mode == 'reply') ? ", topic_replies = topic_replies $sign" : ", topic_first_post_id = $post_id");
	} else {
		$topic_update_sql .= 'topic_vote = 0';
	}

	$sql = 'UPDATE _forums SET ' . $forum_update_sql . ' 
		WHERE forum_id = ' . $forum_id;
	sql_query($sql);

	if ($topic_update_sql != '')
	{
		$sql = "UPDATE _forum_topics SET
			$topic_update_sql
			WHERE topic_id = $topic_id";
		sql_query($sql);
	}

	if ($mode != 'poll_delete')
	{
		$sql = "UPDATE _members
			SET user_posts = user_posts $sign
			WHERE user_id = $user_id";
		sql_query($sql);
	}
	
	$current_time = time();
	$minutes = date('is', $current_time);
	$hour_now = $current_time - (60 * ($minutes[0] . $minutes[1])) - ($minutes[2] . $minutes[3]);

	$sql = "UPDATE _site_stats
		SET " . (($mode == 'newtopic' || $post_data['first_post']) ? 'new_topics = new_topics' : 'new_posts = new_posts') . $sign . '
		WHERE date = ' . intval($hour_now);
	sql_query($sql);
	
	if (!sql_affectedrows()) {
		$sql = 'INSERT INTO _site_stats (date, '.(($mode == 'newtopic' || $post_data['first_post']) ? 'new_topics': 'new_posts').') 
			VALUES (' . $hour_now . ', 1)';
		sql_query($sql);
	}

	$sql = 'SELECT ug.user_id, g.group_id as g_id, u.user_posts, g.group_count, g.group_count_max FROM _groups g, _members u 
		LEFT JOIN _members_group ug ON g.group_id = ug.group_id AND ug.user_id = ?
		WHERE u.user_id = ?
		AND g.group_single_user = 0 
		AND g.group_count_enable = 1
		AND g.group_moderator <> ?';
	$result = sql_rowset(sql_filter($sql, $user_id, $user_id, $user_id));
	
	foreach ($result as $group_data) {
		$user_already_added = (empty($group_data['user_id'])) ? false : true;
		$user_add = ($group_data['group_count'] == $group_data['user_posts'] && $user_id!=GUEST) ? true : false;
		$user_remove = ($group_data['group_count'] > $group_data['user_posts'] || $group_data['group_count_max'] < $group_data['user_posts']) ? true : false;
		
		//user join a autogroup
		if ($user_add && !$user_already_added) {
			$sql_insert = array(
				'group_id' => $group_data['g_id'],
				'user_id' => $user_id,
				'user_pending' => 0
			);
			sql_insert('members_group', $sql_insert);
		}
		else
		if ( $user_already_added && $user_remove)
		{
			//remove user from auto group
			$sql = 'DELETE FROM _members_group
				WHERE group_id = ? 
				AND user_id = ?';
			sql_query(sql_filter($sql, $group_data['g_id'], $user_id));
		}
	}
	
	return;
}

//
// Delete a post/poll
//
function delete_post($mode, &$post_data, &$message, &$meta, &$forum_id, &$topic_id, &$post_id, &$poll_id)
{
	global $config, $lang, $userdata, $user_ip;

	if ($mode != 'poll_delete')
	{
		$sql = 'DELETE FROM _forum_posts
			WHERE post_id = ?';
		sql_query(sql_filter($sql, $post_id));

		if ($post_data['last_post'])
		{
			if ($post_data['first_post'])
			{
				$forum_update_sql .= ', forum_topics = forum_topics - 1';
				
				$sql = 'DELETE FROM _forum_topics
					WHERE topic_id = ?';
				sql_query(sql_filter($sql, $topic_id));

				$sql = 'DELETE FROM _forum_topics_fav
					WHERE topic_id = ?';
				sql_query(sql_filter($sql, $topic_id));
				
				delete_all_unread(UH_T, $topic_id);
			}
		}
	}

	if ($mode == 'poll_delete' || ($mode == 'delete' && $post_data['first_post'] && $post_data['last_post']) && $post_data['has_poll'] && $post_data['edit_poll'])
	{
		$sql = 'DELETE FROM _poll_options
			WHERE topic_id = ?';
		sql_query(sql_filter($sql, $topic_id));

		$sql = 'DELETE FROM _poll_results
			WHERE vote_id = ?';
		sql_query(sql_filter($sql, $poll_id));

		$sql = 'DELETE FROM _poll_voters
			WHERE vote_id = ?';
		sql_query(sql_filter($sql, $poll_id));
	}

	if ($mode == 'delete' && $post_data['first_post'] && $post_data['last_post'])
	{
		$meta = '<meta http-equiv="refresh" content="3;url=' . s_link('forum', $forum_id) . '">';
		$message = $lang['Deleted'];
	}
	else
	{
		$meta = '<meta http-equiv="refresh" content="3;url=' . s_link('topic', $topic_id) . '">';
		$message = (($mode == 'poll_delete') ? $lang['Poll_delete'] : $lang['Deleted']) . '<br /><br />' . sprintf($lang['Click_return_topic'], '<a href="' . s_link('topic', $topic_id) . '">', '</a>');
	}

	$message .=  '<br /><br />' . sprintf($lang['Click_return_forum'], '<a href="' . s_link('forum', $forum_id) . '">', '</a>');
	
	return;
}

//
// Handle user notification on new post
//
function user_notification($mode, &$post_data, &$topic_title, &$forum_id, &$topic_id, &$post_id, &$notify_user)
{
	global $config, $lang, $userdata, $user_ip;

	$current_time = time();

	if ($mode == 'delete')
	{
		$delete_sql = (!$post_data['first_post'] && !$post_data['last_post']) ? sql_filter(' AND user_id = ? ', $userdata['user_id']) : '';
		
		$sql = 'DELETE FROM _forum_topics_fav WHERE topic_id = ?' . $delete_sql;
		sql_query(sql_filter($sql, $topic_id));
	}
	else 
	{
		if ($mode == 'reply')
		{
			$sql = 'SELECT ban_userid
				FROM _banlist';
			$result = sql_rowset($sql);
			
			$user_id_sql = '';
			foreach ($result as $row) {
				if (isset($row['ban_userid']) && !empty($row['ban_userid'])) {
					$user_id_sql .= ', ' . $row['ban_userid'];
				}
			}
			
			$update_watched_sql = '';
			$bcc_list_ary = w();
			$usr_list_ary = w();
			
			$sql = 'SELECT DISTINCT u.user_id, u.user_email, u.user_lang 
				FROM _forum_topics_fav tw
				INNER JOIN _members u ON tw.user_id = u.user_id
				INNER JOIN _members_group ug ON tw.user_id = ug.user_id
				LEFT OUTER JOIN _auth_access aa ON ug.group_id = aa.group_id, _forums f
				WHERE tw.topic_id = ? 
					AND tw.user_id NOT IN (??, ??, ??) 
					AND tw.notify_status = ? 
					AND f.forum_id = ?
					AND u.user_active = 1
					AND (
						(aa.forum_id = ? AND aa.auth_read = 1)
						OR f.auth_read <= ? 
						OR (u.user_level = ? AND f.auth_read = ?)
						OR u.user_level = ?
					)';
			if ($result = sql_rowset(sql_filter($sql, $topic_id, $userdata['user_id'], GUEST, $user_id_sql, TOPIC_WATCH_UN_NOTIFIED, $forum_id, $forum_id, AUTH_REG, USER_MOD, AUTH_MOD, USER_ADMIN))) {
				@set_time_limit(60);
				
				foreach ($result as $row) {
					if ($row['user_email'] != '') {
						$bcc_list_ary[$row['user_lang']][] = $row['user_email'];
					}
					
					$update_watched_sql .= ($update_watched_sql != '') ? ', ' . $row['user_id'] : $row['user_id'];
				}
				
				if (sizeof($bcc_list_ary)) {
					$emailer = new emailer();

					$server_name = trim($config['server_name']);
					$server_protocol = ($config['cookie_secure']) ? 'https://' : 'http://';
					
					$post_url = $server_protocol . $server_name . s_link('post', $post_id) . "#$post_id";
					
					$emailer->from($config['board_email']);
					$emailer->replyto($config['board_email']);

					$topic_title = unprepare_message($topic_title);

					@reset($bcc_list_ary);
					while (list($user_lang, $bcc_list) = each($bcc_list_ary))
					{
						$emailer->use_template('topic_notify', $user_lang);
		
						for ($i = 0; $i < count($bcc_list); $i++)
						{
							$emailer->bcc($bcc_list[$i]);
						}

						// The Topic_reply_notification lang string below will be used
						// if for some reason the mail template subject cannot be read 
						// ... note it will not necessarily be in the posters own language!
						$emailer->set_subject($lang['Topic_reply_notification']); 
						
						// This is a nasty kludge to remove the username var ... till (if?)
						// translators update their templates
						$emailer->msg = preg_replace('#[ ]?{USERNAME}#', '', $emailer->msg);

						$emailer->assign_vars(array(
							'EMAIL_SIG' => '',
							'SITENAME' => $config['sitename'],
							'TOPIC_TITLE' => $topic_title, 

							'U_TOPIC' => $post_url,
							'U_STOP_WATCHING_TOPIC' => $server_protocol . $server_name . $script_name . '&' . POST_TOPIC_URL . "=$topic_id&unwatch=topic")
						);

						$emailer->send();
						$emailer->reset();
					}
				}
			}

			if ($update_watched_sql != '')
			{
				$sql = 'UPDATE _forum_topics_fav
					SET notify_status = ?
					WHERE topic_id = ?
						AND user_id IN (??)';
				sql_query(sql_filter($sql, TOPIC_WATCH_NOTIFIED, $topic_id, $update_watched_sql));
			}
		}

		$sql = 'SELECT topic_id 
			FROM _forum_topics_fav
			WHERE topic_id = ?
				AND user_id = ?';
		if ($row = sql_fieldrow(sql_filter($sql, $topic_id, $userdata['user_id']))) {
			if (!$notify_user && !empty($row['topic_id'])) {
				$sql = 'DELETE FROM _forum_topics_fav
					WHERE topic_id = ?
						AND user_id = ?';
				sql_query(sql_filter($sql, $topic_id, $userdata['user_id']));
			} else if ($notify_user && empty($row['topic_id'])) {
				$sql = "INSERT INTO _forum_topics_fav (user_id, topic_id, notify_status)
					VALUES (" . $userdata['user_id'] . ", $topic_id, 0)";
				sql_query($sql);
			}
		}
	}
}

//
// Username search
//
function username_search($search_match)
{
	global $config, $template, $lang, $images, $themeset, $starttime, $gen_simple_header, $admin_level, $level_prior;
	
	$gen_simple_header = true;

	$username_list = '';
	if (!empty($search_match)) {
		$username_search = preg_replace('/\*/', '%', get_username_base($search_match));

		$sql = 'SELECT username
			FROM _members
			WHERE username LIKE ?
				AND user_id <> ?
			ORDER BY username';
		if (!$result = sql_rowset(sql_filter($sql, $username_search, GUEST))) {
			$username_list .= '<option>' . $lang['No_match']. '</option>';
		}
		
		foreach ($result as $row) {
			$username_list .= '<option value="' . $row['username'] . '">' . $row['username'] . '</option>';
		}
	}
	
	$template->set_filenames(array(
		'body' => 'search_username.htm')
	);

	v_style(array(
		'USERNAME' => (!empty($search_match)) ? get_username_base($search_match) : '', 

		'L_CLOSE_WINDOW' => $lang['Close_window'], 
		'L_SEARCH_USERNAME' => $lang['Find_username'], 
		'L_UPDATE_USERNAME' => $lang['Select_username'], 
		'L_SELECT' => $lang['Select'], 
		'L_SEARCH' => $lang['Search'], 
		'L_SEARCH_EXPLAIN' => $lang['Search_author_explain'], 
		'L_CLOSE_WINDOW' => $lang['Close_window'], 

		'S_USERNAME_OPTIONS' => $username_list, 
		'S_SEARCH_ACTION' => "search.php?mode=searchuser")
	);

	if ($username_list != '') {
		_style('switch_select_name');
	}
	
	return page_footer();
}

?>