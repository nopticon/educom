<?php

if (!defined('IN_APP')) exit;

class __event_delete extends mac {
	public function __construct() {
		parent::__construct();

		$this->auth('colab_admin');
	}

	public function _home() {
		global $config, $user, $cache;

		if (!_button()) {
			return;
		}

		$v = _request(array('event' => 0));

		$sql = 'SELECT *
			FROM _events
			WHERE id = ?';
		if (!$object = sql_fieldrow(sql_filter($sql, $v->event))) {
			fatal_error();
		}

		$sql = 'DELETE FROM _events
			WHERE id = ?';
		sql_query(sql_filter($sql, $v->event));

		return redirect(s_link('events'));
	}
}
