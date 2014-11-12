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
class __radio extends common {
	var $_no = true;
	var $methods = array();
	
	function home() {
		global $user, $nucleo, $style;
		
		$v_today = getdate();
		
		$sql = 'SELECT *
			FROM _radio
			ORDER BY show_day, show_start';
		$shows = $this->_rowset($sql);
		
		$radio = array();
		foreach ($shows as $row) {
			$row['show_start'] = mktime(($row['show_start'] + $user->data['user_timezone'] + $user->data['user_dst']), 0, 0, 0, 0, 0);
			$row['show_end'] = mktime(($row['show_end'] + $user->data['user_timezone'] + $user->data['user_dst']), 0, 0, 0, 0, 0);
			
			$row['show_start'] = (int) date('G', $row['show_start']);
			$row['show_end'] = (int) date('G', $row['show_end']);
			
			$show_end_prev = $row['show_end'];
			$insert = false;
			if (($row['show_end'] > 0) && ($row['show_end'] < $row['show_start'])) {
				$insert = true;
				$row['show_end'] = 0;
			}
			
			$row['show_end'] = ($row['show_end']) ? $row['show_end'] : 24;
			
			if ($row['show_end'] < $row['show_start']) {
				$row['show_day'] = $row['show_day'] + 1;
			}
			
			$radio[$row['show_day']][$row['show_start']] = $row;
			
			if ($insert) {
				$row['show_day'] = $row['show_day'] + 1;
				$row['show_day'] = ($row['show_day'] < 8) ? $row['show_day'] : 1;
				$row['show_start'] = 0;
				$row['show_end'] = $show_end_prev;
				$radio[$row['show_day']][$row['show_start']] = $row;
			}
		}
		
		$sql = 'SELECT *
			FROM _radio_dj d, _members m
			WHERE d.dj_uid = m.user_id
			ORDER BY m.username';
		$djs = $this->_rowset($sql, 'dj_show', false, true);
		
		//
		$days = array(1 => 'Monday', 2 => 'Tuesday', 3 => 'Wednesday', 4 => 'Thursday', 5 => 'Friday', 6 => 'Saturday', 7 => 'Sunday');
		$hours = array(0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17, 18, 19, 20, 21, 22, 23, 24);
		
		foreach ($days as $i => $dayname) {
			if ($dayname == $v_today['weekday']) {
				$weekday = $i;
			}
		}
		
		$open = false;
		$open_level = 0;
		foreach ($radio as $d => $row_day) {
			$style->assign_block_vars('day', array(
				'V_DAY' => $d,
				'V_NAME' => $user->lang['datetime'][$days[$d]],
				'V_WEEKDAY' => $weekday)
			);
			
			$free_start = $s_tail = false;
			$free_end = $tail_hour = 0;
			
			foreach ($hours as $h) {
				$is_free = true;
				$is_rowday = isset($row_day[$h]);
				
				if ($is_rowday || $open !== false) {
					if ($open !== false) {
						if ($h < $open['show_end']) {
							$is_free = false;
							$open_level++;
						} else {
							if ($free_start !== false) {
								$style->assign_block_vars('day.row', array(
									'V_NAME' => (($free_start) ? ($free_start - 1) : $free_start) . ':00 - ' . ($free_end + 1) . ':00',
									'V_FREE' => true)
								);
								
								$free_start = false;
									$free_end = 0;
								
								if (!$is_rowday) {
									continue;
								}
							}
							
							$style->assign_block_vars('day.row', array(
								'V_NAME' => $open['show_name'],
								'V_START' => $open['show_start'] . ':00',
								'V_END' => $open['show_end'] . ':00',
								'V_LEVEL' => $open_level,
								'V_FREE' => false)
							);
							
							if (isset($djs[$open['show_id']])) {
								foreach ($djs[$open['show_id']] as $row) {
									$style->assign_block_vars('day.row.dj', array(
										'V_USERNAME' => $row['username'],
										'V_URL' => $nucleo->link('m', $row['username_base']),
										'V_AVATAR' => $nucleo->config['avatar_path'] . '/' . $row['user_avatar']
									));
								}
							}
							
							$open = false;
							$open_level = 0;
						}
					}
					
					if ($is_rowday) {
						$open_level = 1;
						$open = $row_day[$h];
						$is_free = false;
					}
				}
				
				if ($is_free) {
					if ($free_start === false) {
						$free_start = $h;
					}
					$free_end = $h;
				}
			}
			
			if ($free_start < $h) {
				$style->assign_block_vars('day.row', array(
					'V_NAME' => $free_start . ':00 - ' . $h . ':00',
					'V_FREE' => true)
				);
			}
		}
		
		// Radio forums
		$sql = "SELECT t.*, u.user_id, u.username, u.username_base, u2.user_id as user_id2, u2.username as username2, u2.username_base as username_base2, p.post_text, p.post_username, p2.post_username AS post_username2, p2.post_time
			FROM _forum_topics t, _members u, _forum_posts p, _forum_posts p2, _members u2
			WHERE t.forum_id = 25
				AND t.topic_poster = u.user_id
				AND p.post_id = t.topic_first_post_id
				AND p2.post_id = t.topic_last_post_id
				AND u2.user_id = p2.poster_id
				AND t.topic_announce = 0
			ORDER BY t.topic_important DESC, p2.post_time DESC
			LIMIT 0, 5";
		$topics = $this->_rowset($sql);
		
		foreach ($topics as $i => $row) {
			if (!$i) {
				/*
				define('IN_NUCLEO', true);
				require('./orion/interfase/comments.php');
				$comments = new _comments();
				*/
				$style->assign_block_vars('topics', array());
			}
			
			$style->assign_block_vars('topics.row', array(
				'V_SUBJECT' => $row['topic_title'],
				'V_POST' => /*$comments->parse_message*/nl2br($row['post_text']),
				'U_TOPIC' => $nucleo->link('topic', $row['topic_id']))
			);
		}
		
		$dj_control = false;
		if ($user->data['is_member'] && !$user->data['is_bot']) {
			$sql = 'SELECT *
				FROM _team_members
				WHERE team_id = 4
					AND member_id = ' . (int) $user->data['user_id'];
			if ($this->_fieldrow($sql)) {
				$dj_control = true;
			}
		}
		
		if ($dj_control || $user->data['is_founder']) {
			$style->assign_block_vars('is_dj', array(
				'U_CONNECT' => $nucleo->link('kick'))
			);
		} else {
			$style->assign_block_vars('no_dj', array());
		}
		
		return;
	}
}