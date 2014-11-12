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

class chat {
	public $data = array();
	public $rooms = array();
	public $users = array();
	
	public function __construct() {
		return;
	}
	
	public function m_rooms() {
		$cat = $this->get_cats();
		
		if (!count($cat)) {
			return;
		}
		
		global $user, $config;
		
		$sql = 'SELECT *
			FROM _chat_ch
			ORDER BY ch_def DESC, ch_type, ch_name';
		if (!$this->rooms = sql_rowset($sql)) {
			return false;
		}
		
		_style('chat');
		
		foreach ($cat as $cat_data) {
			_style('chat.cat', array(
				'LABEL' => $cat_data->cat_name)
			);
			
			$rooms = 0;
			foreach ($this->rooms as $channel) {
				if ($cat_data->cat_id != $channel->cat_id) continue;
				
				$ch_auth = ($channel->ch_auth) ? '* ' : '';
				
				_style('chat.cat.channel', array(
					'VALUE' => $channel->ch_int_name,
					'LABEL' => $ch_auth . $channel->ch_name,
					'SELECTED' => ($channel->ch_def) ? ' selected' : '')
				);
				$rooms++;
			}
			
			if (!$rooms) _style('chat.cat.noch');
		}
		
		return true;
	}
	
	public function get_ch_listing($cat) {
		if (!count($cat)) {
			return;
		}
		
		$sql = 'SELECT ch.*, m.username, m.username_base
			FROM _chat_ch ch, _members m
			WHERE ch.ch_founder = m.user_id
			ORDER BY ch_def DESC, cat_id, ch_users DESC';
		$ch = sql_rowset($sql);
		
		$chatters = 0;
		foreach ($cat as $cat_data) {
			_style('cat', array(
				'NAME' => $cat_data->cat_name)
			);
			
			$rooms = 0;
			foreach ($ch as $ch_data) {
				if ($cat_data->cat_id != $ch_data->cat_id) {
					continue;
				}
				
				$chatters += $ch_data->ch_users;
				
				_style('cat.item', array(
					'U_CHANNEL' => s_link('chat', $ch_data->ch_int_name),
					'CH_NAME' => $ch_data->ch_name,
					'CH_DESC' => $ch_data->ch_desc,
					'CH_USERS' => $ch_data->ch_users,
					'USERNAME' => $ch_data->username,
					'U_USERNAME' => s_link('m', $ch_data->username_base))
				);
				$rooms++;
			}
			
			if (!$rooms) _style('cat.no_rooms');
		}
		
		return $chatters;
	}
	
	public function get_cats() {
		global $cache;
		
		$cat = w();
		if (!$cat = $cache->get('chat_cat')) {
			$sql = 'SELECT *
				FROM _chat_cat
				ORDER BY cat_order';
			$cat = sql_rowset($sql);
			$cache->save('chat_cat', $cat);
		}
		
		return $cat;
	}
	
	public function _setup() {
		global $user;
		
		$ch = request_var('ch', '');
		if (!empty($ch)) {
			if (preg_match('/([0-9a-z\-]+)/', $ch)) {
				$sql = 'SELECT *
					FROM _chat_ch
					WHERE ch_int_name = ?
					LIMIT 1';
				if ($row = sql_fieldrow(sql_filter($sql, $ch))) {
					$row['ch_id'] = (int) $row->ch_id;
					$this->data = $row;
					
					return true;
				}
			}
			
			fatal_error();
		}
		
		return false;
	}
	
	public function process_data($csid, $mode) {
		global $user, $config, $comments;
		
		if (empty($csid)) {
			return false;
		}
		
		/*
		MSG_ID							INT(11)
		MSG_CH							INT(11)
		MSG_IGNORE					INT(11)
		MSG_MEMBER_ID				INT(11)
		MSG_TEXT						TEXT
		MSG_TIME						INT(11)
		MSG_IP							VARCHAR(40)
		*/
		
		$last_msg = request_var('last_msg', 0);
		
		switch ($mode) {
			case 'logout':
				$sql = 'SELECT *
					FROM _chat_sessions
					WHERE session_id = ?';
				if ($row = sql_fieldrow(sql_filter($sql, $csid))) {
					$sql = 'UPDATE _chat_ch
						SET ch_users = ch_users - 1
						WHERE ch_id = ?';
					sql_query(sql_filter($sql, $row->session_ch_id));
					
					$sql = 'DELETE FROM _chat_sessions
						WHERE session_id = ?';
					sql_query(sql_filter($sql, $csid));
					
					$this->_message($row->session_ch_id, $user->d('user_id'), sprintf(lang('chat_member_logout'), $user->d('username')));
				}
				
				redirect(s_link('chat'));
				break;
			case 'send':
				$message = request_var('message', '');
				
				if (empty($message)) {
					return false;
				}
				
				$this->_message($this->data->ch_id, 0, $message);
				
			case 'get':
				$sql = 'SELECT c.*, m.username
					FROM _chat_msg c, _members m
					WHERE c.msg_ch = ?
						AND c.msg_ignore <> ?
						AND c.msg_id > ?
						AND c.msg_time > ?
						AND c.msg_member_id = m.user_id
					ORDER BY c.msg_time ASC';
				$messages = sql_rowset(sql_filter($sql, $this->data->ch_id, $user->d('user_id'), $last_msg, $this->data->session_start));
				
				$sql = 'SELECT m.user_id, m.username, m.username_base
					FROM _chat_sessions s, _members m
					WHERE s.session_ch_id = ?
						AND s.session_member = m.user_id
					ORDER BY m.username';
				$members = sql_rowset(sql_filter($sql, $this->data->ch_id));
				
				$so_messages = count($messages);
				$so_members = count($members);
				
				//
				if ($so_messages || $so_members) {
					header("Expires: Mon, 26 Jul 1997 05:00:00 GMT" ); 
					header("Last-Modified: " . gmdate( "D, d M Y H:i:s" ) . "GMT" ); 
					header("Cache-Control: no-cache, must-revalidate" ); 
					header("Pragma: no-cache" );
					header("Content-Type: text/xml; charset=iso-8859-1");
					
					$xmlre = '<?xml version="1.0" ?><ROOT>';
					
					if ($so_messages) {
						foreach ($messages as $row) {
							$message = $comments->parse_message($row->msg_text, 'bold red');
							
							$xmlre .= '<message id="' . $row->msg_id . '" sid="' . $this->data->session_id . '">';
							
							if (!$row['msg_ignore']) {
								if (preg_match("#\b(" . $user->d('username') . ")\b#i", $message)) {
									$message = '<span class="rkc_self">' . str_replace('\"', '"', substr(@preg_replace('#(\>(((?>([^><]+|(?R)))*)\<))#se', "@preg_replace('#\b(" . str_replace('\\', '\\\\', $user->d('username')) . ")\b#i', '<span class=\"sgray bold\">\\\\1</span>', '\\0')", '>' . $message . '<'), 1, -1)) . '</span>';
								}
								
								$message = '<strong>&lt;' . $row->username . '&gt;</strong> ' . $message . '<br />';
							}
							
							$xmlre .= '<smsg>' . rawurlencode($message) . '</smsg>';
							$xmlre .= '</message>';
						}
					}
					
					if ($so_members) {
						foreach ($members as $row) {
							$xmlre .= '<member user_id="' . $row->user_id . '">';
							$xmlre .= '<nick>' . $row->username . '</nick><prof>' . s_link('m', $row->username_base) . '</prof>';
							$xmlre .= '</member>';
						}
					}
					
					$xmlre .= '</ROOT>';
					echo $xmlre;
				}
				
				/*
				
				$sql = 'SELECT c.*, m.username
					FROM _chat_msg c, _members m
					WHERE c.msg_ch = ?
						AND c.msg_ignore <> ?
						AND c.msg_id > ?
						AND c.msg_time > ?
						AND c.msg_member_id = m.user_id
					ORDER BY c.msg_time ASC';
				$result = sql_rowset(sql_filter($sql, $this->data['ch_id'], $user->d('user_id'), $last_msg, $this->data['session_start']));
				
				*/
				break;
		}
		
		return;
	}
	
	public function auth() {
		global $user;
		
		if ($user->is('founder') || ($this->data->ch_founder == $user->d('user_id'))) {
			return true;
		}
		
		//
		// Check friends
		//
		if ($this->data->ch_auth == 2) {
			$sql = 'SELECT *
				FROM _members_friends
				WHERE (user_id = ? AND buddy_id = ?)
					OR (user_id = ? AND buddy_id = ?)';
			if (sql_fieldrow(sql_filter($sql, $this->data->ch_founder, $user->d('user_id'), $user->d('user_id'), $this->data->ch_founder))) {
				return true;
			}

			return false;
		}
		
		/*
		0 - No Access
		1 - Founder
		2 - Member
		*/
		
		$sql = 'SELECT ch_auth
			FROM _chat_auth
			WHERE ch_id = ?
				AND ch_user_id = ?';
		if ($ch_auth = sql_field(sql_filter($sql, $this->data->ch_id, $user->d('user_id')), 'ch_auth', 0)) {
			switch ($ch_auth) {
				case 0:
					return false;
					break;
				case 1:
					$this->data->is_founder = true;
					break;
				case 2:
					$this->data->is_member = true;
					break;
			}
		} else {
			if ($this->data->ch_auth) {
				return false;
			}
		}
		
		return true;
	}
	
	public function session($sid) {
		global $user, $config;
		
		/*
		SESSION_ID				VARCHAR(32)
		SESSION_MEMBER		MEDIUMINT(8)
		SESSION_CH_ID			MEDIUMINT(8)
		SESSION_IP				VARCHAR(8)
		SESSION_START			INT(11)
		SESSION_TIME			INT(11)
		SESSION_LAST_MESSAGE	INT(11)
		*/
		
		$ttime = time();
		
		$updated = false;
		
		$sql = 'SELECT *
			FROM _chat_sessions
			WHERE session_member = ?
				AND session_ch_id = ?';
		if ($row = sql_fieldrow(sql_filter($sql, $user->d('user_id'), $this->data->ch_id))) {
			$last_msg = request_var('last_msg', 0);
			
			$sql = 'UPDATE _chat_sessions SET session_time = ?, session_last_msg = ?
				WHERE session_id = ? AND session_member = ? AND session_ch_id = ?';
			sql_query(sql_filter($sql, $ttime, $last_msg, $sid, $user->d('user_id'), $this->data->ch_id));
			
			$row->session_time = $ttime;
			$row->session_last_msg = $last_msg;
			$this->data += $row;
			
			$updated = true;
		}
		
		if ($updated) {
			return true;
		}
		
		$insert_data = array(
			'session_id' => md5(unique_id()),
			'session_member' => (int) $user->d('user_id'),
			'session_ch_id' => (int) $this->data->ch_id,
			'session_ip' => $user->ip,
			'session_start' => (int) $ttime,
			'session_time' => (int) $ttime,
			'session_last_msg' => 0
		);
		sql_insert('chat_sessions', $insert_data);
		
		$sql = 'UPDATE _chat_ch SET ch_users = ch_users + 1
			WHERE ch_id = ?';
		sql_query(sql_filter($sql, $this->data->ch_id));
		
		$this->_message($this->data->ch_id, $user->d('user_id'), sprintf(lang('chat_member_entered'), $user->d('username')));
		
		$this->data += $insert_data;
		$this->sys_clean();
		
		return;
	}
	
	public function _message($ch, $ignore, $message) {
		global $user, $comments;
		
		$insert_data = array(
			'msg_ch' => (int) $ch,
			'msg_ignore' => (int) $ignore,
			'msg_member_id' => (int) $user->d('user_id'),
			'msg_text' => (string) $comments->prepare($message),
			'msg_time' => (int) time(),
			'msg_ip' => $user->ip
		);
		sql_insert('chat_msg', $insert_data);
		
		return $insert_data;
	}
	
	public function window() {
		global $user, $config;
		
		v_style(array(
			'CH_SID' => $this->data->session_id,
			'CH_INT_NAME' => $this->data->ch_int_name,
			'CH_NAME' => $this->data->ch_name)
		);
		
		if ($user->d('user_id') === $this->data->ch_founder) {
			// TEMP
			// _style('ch_manage');
		}
	}
	
	public function sys_clean() {
		global $user;

		$ttime = time();
		
		$sql = 'DELETE FROM _chat_msg
			WHERE msg_time < ?';
		sql_query(sql_filter($sql, ($ttime - 3600)));
		
		//
		//
		$sql = 'SELECT s.*, m.username
			FROM _chat_sessions s, _members m
			WHERE s.session_time < ?
				AND s.session_member = m.user_id';
		if ($result = sql_rowset(sql_filter($sql, ($ttime - 300)))) {
			$update_ch = $delete_sessions = $show_members = w();
			
			foreach ($result as $row) {
				$chid = $row->session_ch_id;
				if (!isset($update_ch[$chid])) {
					$update_ch[$chid] = 0;
				}
				
				$update_ch[$chid]++;
				$show_members[$chid][$row->session_member] = $row->username;
				$delete_sessions[] = "'" . sql_escape($row->session_id) . "'";
			}
			
			foreach ($update_ch as $ch_id => $number) {
				$sql = 'UPDATE _chat_ch
					SET ch_users = ch_users - ??
					WHERE ch_id = ?';
				sql_query(sql_filter($sql, $number, $ch_id));
				
				foreach ($show_members[$ch_id] as $user_id => $username) {
					$this->_message($ch_id, $user_id, sprintf(lang('chat_member_timeout'), $username));
				}
			}
			
			$sql = 'DELETE FROM _chat_sessions
				WHERE session_id IN (??)';
			sql_query(sql_filter($sql, implode(',', $delete_sessions)));
		}
		
		//
		//
		//
		/*
		$sql = 'DELETE FROM _chat_sessions
			WHERE session_start < ?';
		sql_query(sql_filter($sql, ($ttime - 43200)));
		*/
	}
}