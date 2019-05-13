<?php

if (!defined('IN_APP')) exit;

class __forums_topic_points extends mac {
	private $id;

	public function __construct() {
		parent::__construct();

		$this->auth('mod');
	}

	public function _home() {
		global $config, $user, $cache;

		$this->id = request_var('msg_id', 0);

		$sql = 'SELECT *
			FROM _forum_topics
			WHERE topic_id = ?';
		if (!$this->object = sql_fieldrow(sql_filter($sql, $this->id))) {
			fatal_error();
		}

		$this->object = (object) $this->object;

		$this->object->new_value = ($this->object->topic_points) ? 0 : 1;
		topic_arkane($this->id, $this->object->new_value);

		$sql_insert = array(
			'bio' => $user->d('user_id'),
			'time' => time(),
			'ip' => $user->ip,
			'action' => 'points',
			'old' => $this->object->topic_points,
			'new' => $this->object->new_value
		);
		sql_insert('log_mod', $sql_insert);

		return redirect(s_link('topic', $topic_id));
	}
}
