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

class __forums_topic_move extends mac {
	private $from;
	private $to;
	
	public function __construct() {
		parent::__construct();
		
		$this->auth('mod');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		if (!_button()) {
			$sql = 'SELECT forum_id, forum_name
				FROM _forums
				ORDER BY forum_order';
			$result = sql_rowset($sql);
			
			foreach ($result as $i => $row) {
				if (!$i) _style('forums');
				
				_style('forums.row', array(
					'FORUM_ID' => $row->forum_id,
					'FORUM_NAME' => $row->forum_name)
				);
			}
			
			return false;
		}
		
		$t = request_var('topic_id', 0);
		$f = request_var('forum_id', 0);
		
		if (!$f || !$t) {
			fatal_error();
		}
		
		//
		$sql = 'SELECT *
			FROM _forum_topics
			WHERE topic_id = ?';
		if (!$tdata = sql_fieldrow(sql_filter($sql, $t))) {
			fatal_error();
		}
		
		//
		$sql = 'SELECT *
			FROM _forums
			WHERE forum_id = ?';
		if (!$fdata = sql_fieldrow(sql_filter($sql, $f))) {
			fatal_error();
		}
		
		//
		$sql = 'UPDATE _forum_topics SET forum_id = ?
			WHERE topic_id = ?';
		sql_query(sql_filter($sql, $f, $t));
		
		$sql = 'UPDATE _forum_posts SET forum_id = ?
			WHERE topic_id = ?';
		sql_query(sql_filter($sql, $f, $t));
		
		if (in_array($f, array(20, 39))) {
			topic_feature($t, 0);
			topic_arkane($t, 0);
		}
		
		sync_topic_move($f);
		sync_topic_move($tdata->forum_id);
		
		//redirect(s_link('forum', $f));
		
		return;
	}
}

function sync_topic_move($id) {
	$last_topic = 0;
	$total_posts = 0;
	$total_topics = 0;
	
	//
	$sql = 'SELECT COUNT(post_id) AS total 
		FROM _forum_posts
		WHERE forum_id = ?';
	$total_posts = sql_field(sql_filter($sql, $id), 'total', 0);
	
	$sql = 'SELECT MAX(topic_id) as last_topic, COUNT(topic_id) AS total
		FROM _forum_topics
		WHERE forum_id = ?';
	if ($row = sql_fieldrow(sql_filter($sql, $id))) {
		$last_topic = $row->last_topic;
		$total_topics = $row->total;
	}
	
	//
	$sql = 'UPDATE _forums SET forum_last_topic_id = ?, forum_posts = ?, forum_topics = ?
		WHERE forum_id = ?';
	sql_query(sql_filter($sql, $last_topic, $total_posts, $total_topics, $id));
	
	return;
}