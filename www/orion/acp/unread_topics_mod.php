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

class __unread_topics_mod extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('founder');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		$auth = array(16 => 'radio', 17 => 'mod');
		
		$sql = 'SELECT *
			FROM _members_unread
			WHERE element = 8
			ORDER BY user_id, element, item';
		$result = sql_rowset($sql);
		
		foreach ($result as $row) {
			$delete = false;
			
			if ($t = search_topic($row->item)) {
				if (in_array($t->forum_id, array(16, 17))) {
					$a = $user->is($auth[$t->forum_id], $row->user_id);
					if (!$a) {
						$delete = true;
					}
				}
			} else {
				$delete = true;
			}
			
			if ($delete) {
				$sql = 'DELETE LOW_PRIORITY FROM _members_unread
					WHERE user_id = ?
						AND element = 8
						AND item = ?';
				sql_query(sql_filter($sql, $row->user_id, $row->item));
			}
		}
		
		return _pre('Finished.', true);
	}
}

//
function search_topic($topic_id) {
	$result = false;
	
	$sql = 'SELECT *
		FROM _forum_topics
		WHERE topic_id = ?';
	if ($row = sql_fieldrow(sql_filter($sql, $topic_id))) {
		$result = $row;
	}
	
	return $result;
}