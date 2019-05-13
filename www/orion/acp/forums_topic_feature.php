<?php

if (!defined('IN_APP')) exit;

class __forums_topic_feature extends mac {
	private $id;

	public function __construct() {
		parent::__construct();

		$this->auth('mod');
	}

	public function _home() {
		global $config, $user, $cache;

		if (!_button()) {
			return;
		}

		$this->id = request_var('msg_id', 0);

		$sql = 'SELECT *
			FROM _forum_topics
			WHERE topic_id = ?';
		if (!$this->object = sql_fieldrow(sql_filter($sql, $this->id))) {
			fatal_error();
		}

		$this->object = (object) $this->object;

		$this->object->new_value = ($this->object->topic_featured) ? 0 : 1;
		topic_feature($this->id, $this->object->new_value);

		$sql_insert = array(
			'bio' => $user->d('user_id'),
			'time' => time(),
			'ip' => $user->ip,
			'action' => 'feature',
			'old' => $this->object->topic_featured,
			'new' => $this->object->new_value
		);
		sql_insert('log_mod', $sql_insert);

		return redirect(s_link('topic', $this->id));
	}
}
