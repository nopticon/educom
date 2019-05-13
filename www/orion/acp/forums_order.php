<?php

if (!defined('IN_APP')) exit;

class __forum_order extends mac {
	public function __construct() {
		parent::__construct();

		$this->auth('founder');
	}

	public function _home() {
		global $config, $user, $cache;

		if (!_button()) {
			$sql = 'SELECT forum_id, forum_name
				FROM _forums
				ORDER BY forum_order ASC';
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

		$list = request_var('listContainer', array(0));

		$orderid = 10;
		foreach ($list as $catid) {
			$sql = 'UPDATE _forums SET forum_order = ?
				WHERE forum_id = ?';
			sql_query(sql_filter($sql, $orderid, $catid));

			$orderid += 10;
		}

		_pre('Update.', true);
	}
}
