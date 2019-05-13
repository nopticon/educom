<?php

if (!defined('IN_APP')) exit;

class __forums_topic_case extends mac {
	public function __construct() {
		parent::__construct();

		$this->auth('mod');
	}

	public function _home() {
		$this->__home();

		return $this->warning_show();
	}

	private function __home() {
		global $config, $user, $cache;

		if (!_button()) {
			return false;
		}

		if (!$topic_id = request_var('topic_id', 0)) {
			fatal_error();
		}

		$sql = 'SELECT *
			FROM _forum_topics
			WHERE topic_id = ?';
		if (!$data = sql_fieldrow(sql_filter($sql, $topic_id))) {
			fatal_error();
		}

		$title = ucfirst(strtolower($data->topic_title));

		$sql = 'UPDATE _forum_topics SET topic_title = ?
			WHERE topic_id = ?';
		sql_query(sql_filter($sql, $title, $topic_id));

		return redirect(s_link('topic', $topic_id));
	}
}
