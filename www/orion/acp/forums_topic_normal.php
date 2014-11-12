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

class __forums_topic_normal extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('mod');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		if (_button()) {
			$topic = request_var('topic', 0);
			
			$sql = 'SELECT *
				FROM _forum_topics
				WHERE topic_id = ?';
			if (!$topicdata = sql_fieldrow(sql_filter($sql, $topic))) {
				fatal_error();
			}
			
			$sql = 'UPDATE _forum_topics
				SET topic_color = ?, topic_announce = 0, topic_important = 0
				WHERE topic_id = ?';
			sql_query(sql_filter($sql, '', $topic));
			
			_style('updated', array(
				'MESSAGE' => 'El tema <strong>' . $topicdata->topic_title . '</strong> ha sido normalizado.')
			);
		}
		
		$sql = 'SELECT t.topic_id, t.topic_title, f.forum_name
			FROM _forums f, _forum_topics t
			WHERE f.forum_id = t.forum_id
				AND (topic_announce = 1
				OR topic_important = 1)
			ORDER BY forum_order, topic_title';
		$topics = sql_rowset($sql);
		
		$forum_name = '';
		foreach ($topics as $i => $row) {
			if (!$i) _style('topics');
			
			if ($forum_name != $row->forum_name) _style('topics.forum', array('FORUM_NAME' => $row->forum_name));
			
			$forum_name = $row->forum_name;
			
			_style('topics.forum.row', array(
				'TOPIC_ID' => $row->topic_id,
				'TOPIC_TITLE' => $row->topic_title)
			);
		}
		
		return;
	}
}