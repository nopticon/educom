<?php

if (!defined('IN_APP')) exit;

class __forums_topic_announce extends mac {
	public function __construct() {
		parent::__construct();

		$this->auth('founder');
	}

	public function _home() {
		global $config, $user, $cache;

		if (!_button()) {
			return false;
		}

		$topic = request_var('topic', 0);
		$important = request_var('important', 0);

		$sql = 'SELECT *
			FROM _forum_topics
			WHERE topic_id = ?';
		if (!$topicdata = sql_fieldrow(sql_filter($sql, $topic))) {
			fatal_error();
		}

		$sql_important = ($important) ? ', topic_important = 1' : '';

		$sql = 'UPDATE _forum_topics
			SET topic_color = ?, topic_announce = 1' . $sql_important . '
			WHERE topic_id = ?';
		sql_query(sql_filter($sql, 'E1CB39', $topic));

		return _pre('El tema <strong>' . $topicdata->topic_title . '</strong> ha sido anunciado.', true);
	}
}
