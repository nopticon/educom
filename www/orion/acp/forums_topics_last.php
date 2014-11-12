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

class __forums_topics_last extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('mod');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		$sql = 'SELECT e.event_topic, f.forum_name, t.topic_id, t.topic_title, t.topic_views, t.topic_replies
			FROM _forum_topics t
			LEFT JOIN _events e ON e.event_topic = t.topic_id
			INNER JOIN _forums f ON t.forum_id = f.forum_id
			WHERE t.forum_id  NOT IN (38)
			ORDER BY t.topic_time DESC
			LIMIT 100';
		$result = sql_rowset($sql);
		
		foreach ($result as $i => $row) {
			if (!$i) _style('topics');
			
			_style('topics.row', array(
				'TOPIC_ID' => s_link('topic', $row->topic_id),
				'TOPIC_FORUM' => $row->forum_name,
				'TOPIC_EVENT' => $row->event_topic,
				'TOPIC_TITLE' => $row->topic_title,
				'TOPIC_VIEWS' => $row->topic_views,
				'TOPIC_REPLIES' => $row->topic_replies)
			);
		}
		
		return;
	}
}