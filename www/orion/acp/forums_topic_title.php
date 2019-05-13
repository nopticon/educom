<?php

if (!defined('IN_APP')) exit;

class __forums_topic_title extends mac {
	public function __construct() {
		parent::__construct();

		$this->auth('mod');
	}

	public function _home() {
		global $config, $user, $cache;

		if (!_button()) {
			return false;
		}

		$topic = request_var('topic', 0);
		$title = request_var('title', '');

		$sql = 'SELECT *
			FROM _forum_topics
			WHERE topic_id = ?';
		if (!$topicdata = sql_fieldrow(sql_filter($sql, $topic))) {
			fatal_error();
		}

		$sql = 'UPDATE _forum_topics SET topic_title = ?
			WHERE topic_id = ?';
		sql_query(sql_filter($sql, $title, $topic));

		return _pre('El titulo del tema <strong>' . $topicdata->topic_title . '</strong> ha sido cambiado por <strong>' . $title . '</strong>.', true);
	}
}
