<?php

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
