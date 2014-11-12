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

class community {
	public function __construct() {
		return;
	}
	
	public function run() {
		global $config, $user;
		
		$this->founders();
		$this->team();
		$this->recent_members();
		$this->birthdays();
		
		v_style(array(
			'MEMBERS_COUNT' => number_format($config->max_users))
		);
		
		//
		// Online
		//
		$sql = 'SELECT u.user_id, u.username, u.username_base, u.user_type, u.user_hideuser, s.session_ip
			FROM _members u, _sessions s
			WHERE s.session_time >= ?
				AND u.user_id = s.session_user_id
			ORDER BY u.username ASC, s.session_ip ASC';
		$this->online(sql_filter($sql, ($user->time - (5 * 60))), 'online', 'MEMBERS_ONLINE');
		
		//
		// Today Online
		//
		$minutes = date('is', time());
		$timetoday = (time() - (60 * intval($minutes[0].$minutes[1])) - intval($minutes[2].$minutes[3])) - (3600 * $user->format_date(time(), 'H'));
		
		$sql = 'SELECT user_id, username, username_base, user_hideuser, user_type
			FROM _members
			WHERE user_type NOT IN (??)
				AND user_lastvisit >= ?
				AND user_lastvisit < ? 
			ORDER BY username';
		$this->online(sql_filter($sql, USER_INACTIVE, $timetoday, ($timetoday + 86399)), 'online', 'MEMBERS_TODAY', 'MEMBERS_VISIBLE');
		
		return true;
	}
	
	public function founders() {
		global $cache, $user, $comments;
		
		if (!$founders = $cache->get('founders')) {
			$sql = 'SELECT user_id, username, username_base, user_avatar
				FROM _members
				WHERE user_type = ?
				ORDER BY user_id';
			$result = sql_rowset(sql_filter($sql, USER_FOUNDER));
			
			$founders = w();
			foreach ($result as $row) {
				if ($row->username_base == 'rockrepublik') continue;
				
				$founders[$row->user_id] = $comments->user_profile($row);
			}
			
			$cache->save('founders', $founders);
		}
		
		foreach ($founders as $user_id => $data) {
			_style('founders', array(
				'REALNAME' => $data->username,
				'USERNAME' => $data->username,
				'AVATAR' => $data->user_avatar,
				'PROFILE' => $data->profile)
			);
		}
		
		return;
	}
	
	public function team() {
		global $cache, $comments;
		
		if (!$teams = $cache->get('team')) {
			$sql = 'SELECT *
				FROM _team
				WHERE team_show = 1
				ORDER BY team_order';
			if ($teams = sql_rowset($sql)) {
				$cache->save('team', $teams);
			}
		}
		
		if (!$team = $cache->get('team_members')) {
			$sql = 'SELECT DISTINCT t.*, m.user_id, m.username, m.username_base, m.user_avatar
				FROM _team_members t, _members m
				WHERE t.member_id = m.user_id
				ORDER BY m.username';
			if ($team = sql_rowset($sql)) {
				$cache->save('team_members', $team);
			}
		}
		
		foreach ($team as $i => $row) {
			if (!$i) _style('team');
			
			$profile = $comments->user_profile($row);
			
			_style('team.row', array(
				'USERNAME' => $profile->username,
				'REALNAME' => $profile->real_name,
				'PROFILE' => $profile->profile,
				'AVATAR' => $profile->user_avatar)
			);
		}
		
		return;
	}
	
	public function online($sql, $block, $block_title, $unset_legend = false) {
		global $user;
		static $user_bots;
		
		if (!isset($user_bots)) {
			obtain_bots($bots);
			
			$bots = w();
			foreach ($bots as $row) {
				$user_bots[$row->user_id] = true;
			}
		}
		
		foreach (w('last_user_id users_visible users_hidden users_guests users_bots last_ip users_online') as $v) {
			${$v} = 0;
		}
		
		_style($block, array(
			'L_TITLE' => lang($block_title))
		);
		_style($block . '.members');
		
		$is_founder = $user->is('founder');
		$result = sql_rowset($sql);
		
		foreach ($result as $row) {
			if ($row->user_id != GUEST) {
				if ($row->user_id != $last_user_id) {
					$is_bot = isset($user_bots[$row->user_id]);
					
					if (!$row->user_hideuser) {
						$username = $row->username;
						
						if ($is_bot) {
							$users_bots++;
						} else {
							$users_visible++;
						}
					} else {
						$username = '*' . $row->username;
						$users_hidden++;
					}
					
					if (((!$row->user_hideuser || $is_founder) && !$is_bot) || ($is_bot && $is_founder)) {
						_style($block . '.members.item', array(
							'USERNAME' => $username,
							'PROFILE' => s_link('m', $row->username_base))
						);
					}
				}
				
				$last_user_id = $row->user_id;
			} else {
				if ($row->session_ip != $last_ip) {
					$users_guests++;
				}
				
				$last_ip = $row->session_ip;
			}
		}
		
		$users_total = $users_visible + $users_hidden + $users_guests + $users_bots;
		
		if (!($users_visible + $users_hidden) || (!$users_visible && $users_hidden)) {
			_style($block . '.members.none');
		}
		
		_style($block . '.legend');
		
		$online_ary = array(
			'MEMBERS_TOTAL' => $users_total,
			'MEMBERS_VISIBLE' => $users_visible,
			'MEMBERS_GUESTS' => $users_guests,
			'MEMBERS_HIDDEN' => $users_hidden,
			'MEMBERS_BOT' => $users_bots
		);
		
		if ($unset_legend !== false) {
			unset($online_ary[$unset_legend]);
		}
		
		foreach ($online_ary as $lk => $vk) {
			if (!$vk && $lk != 'MEMBERS_TOTAL') {
				continue;
			}
			
			_style($block . '.legend.item', array(
				'L_MEMBERS' => lang($lk . (($vk != 1) ? '2' : '')),
				'ONLINE_VALUE' => $vk)
			);
		}
		
		return;
	}
	
	public function birthdays() {
		global $comments;
		
		$sql = "SELECT user_id, username, username_base, user_avatar
			FROM _members
			WHERE user_birthday LIKE ?
				AND user_type NOT IN (??)
				AND user_avatar <> ?
			ORDER BY user_posts DESC, username";
		if (!$result = sql_rowset(sql_filter($sql, date('%md'), USER_INACTIVE, ''))) {
			return false;
		}
		
		foreach ($result as $i => $row) {
			if (!$i) _style('birthday');
			
			$profile = $comments->user_profile($row);
			
			_style('birthday.row', array(
				'USERNAME' => $profile->username,
				'PROFILE' => $profile->profile,
				'AVATAR' => $profile->user_avatar)
			);
		}

		return true;
	}
	
	public function recent_members() {
		global $user;
		
		$sql = 'SELECT username, username_base
			FROM _members
			WHERE user_type NOT IN (??)
			ORDER BY user_regdate DESC
			LIMIT 10';
		$result = sql_rowset(sql_filter($sql, USER_INACTIVE));
		
		foreach ($result as $i => $row) {
			if (!$i) _style('recent_members');
			
			_style('recent_members.item', array(
				'USERNAME' => $row->username,
				'PROFILE' => s_link('m', $row->username_base))
			);
		}
		
		return true;
	}
}