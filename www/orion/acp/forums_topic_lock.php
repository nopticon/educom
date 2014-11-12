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

class __forums_topic_lock extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('mod');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		if (!_button()) {
			return false;
		}
		
		$topic_id = request_var('topic', 0);
		
		$sql = 'SELECT *
			FROM _forum_topics
			WHERE topic_id = ?';
		if (!$topicdata = sql_fieldrow(sql_filter($sql, $topic_id))) {
			fatal_error();
		}
		
		$sql = 'UPDATE _forum_topics SET topic_locked = ?
			WHERE topic_id = ?';
		sql_query(sql_filter($sql, !$topicdata->topic_locked, $topic_id));

		return redirect(s_link('topic', $topic_id));
	}
}