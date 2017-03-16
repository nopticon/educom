<?php

if (!defined('IN_APP')) exit;

class __forums_alias_modify extends mac {
	public function __construct() {
		parent::__construct();

		$this->auth('founder');
	}

	public function _home() {
		global $config, $user, $cache;

		if (_button()) {
			$forum_id = request_var('fid', 0);
			$forum_alias = request_var('falias', '');

			$sql = 'UPDATE _forums SET forum_alias = ?
				WHERE forum_id = ?';
			sql_query(sql_filter($sql, $forum_alias, $forum_id));

			redirect(s_link('board', $forum_alias));
		}

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

		return;
	}
}
