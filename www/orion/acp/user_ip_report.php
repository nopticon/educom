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

class __user_ip_report extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('founder');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		$username = request_var('username', '');
		$ip = request_var('ip', '');
		
		if (_button() && ($username || $ip)) {
			if ($username) {
				$username_base = get_username_base($username);
				
				$sql = 'SELECT m.username, l.*
					FROM _members m, _members_iplog l
					WHERE m.user_id = l.log_user_id
						AND m.username_base = ?
					ORDER BY l.log_time DESC';
				$sql = sql_filter($sql, $username_base);
			} else if ($ip) {
				$sql = 'SELECT m.username, l.*
					FROM _members m, _members_iplog l
					WHERE m.user_id = l.log_user_id
						AND l.log_ip = ?
					ORDER BY l.log_time DESC';
				$sql = sql_filter($sql, $ip);
			}
			$result = sql_rowset($sql);
			
			foreach ($result as $i => $row) {
				if (!$i) _style('log');
				
				_style('log.row', array(
					'UID' => $row->log_user_id,
					'USERNAME' => $row->username,
					'TIME' => $user->format_date($row->log_time),
					'ENDTIME' => (($row->log_endtime) ? $user->format_date($row->log_endtime) : '&nbsp;'),
					'DIFFTIME' => (($row->log_endtime) ? _implode(' ', timeDiff($row->log_endtime, $row->log_time, true, 1)) : '&nbsp;'),
					'IP' => $row->log_ip,
					'AGENT' => $row->log_agent)
				);
			}
		}
		
		return;
	}
}

function timeDiff($timestamp, $now = 0, $detailed = false, $n = 0) {
	// If the difference is positive "ago" - negative "away"
	if (!$now) {
		$now = time();
	}
	
	$action = ($timestamp >= $now) ? 'away' : 'ago';
	$diff = ($action == 'away' ? $timestamp - $now : $now - $timestamp);
	
	// Set the periods of time
	$periods = array('s', 'm', 'h', 'd', 's', 'm', 'a');
	$lengths = array(1, 60, 3600, 86400, 604800, 2630880, 31570560);
	
	// Go from decades backwards to seconds
	$result = w();
	
	$i = count($lengths);
	$time = '';
	while ($i >= $n) {
		$item = $lengths[$i - 1];
		if ($diff < $item) {
			$i--;
			continue;
		}
		
		$val = floor($diff / $item);
		$diff -= ($val * $item);
		$result[] = $val . $periods[($i - 1)];
		
		if (!$detailed) {
			$i = 0;
		}
		$i--;
	}
	
	return (count($result)) ? $result : false;
}