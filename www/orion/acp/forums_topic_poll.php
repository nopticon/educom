<?php

if (!defined('IN_APP')) exit;

class __forums_topic_poll extends mac {
	public function __construct() {
		parent::__construct();

		$this->auth('founder');
	}

	public function _home() {
		global $config, $user, $cache;

		if (!_button()) {
			return false;
		}

		if (!$topic_id = request_var('topic_id', '')) {
			fatal_error();
		}

		$sql = 'SELECT *
			FROM _poll_options
			WHERE topic_id = ?';
		if (!$data_opt = sql_fieldrow(sql_filter($sql, $topic_id))) {
			fatal_error();
		}

		$sql = 'SELECT v.*, m.username, r.vote_option_text
			FROM _poll_voters v, _members m, _poll_results r
			WHERE v.vote_id = ?
				AND v.vote_id = r.vote_id
				AND v.vote_user_id = m.user_id
				AND r.vote_option_id = v.vote_cast';
		$result = sql_rowset(sql_filter($sql, $data_opt->vote_id));

		echo '<table>';

		foreach ($result as $row) {
			echo '<tr>
			<td>' . $row->username . '</td>
			<td>' . $row->vote_option_text . '</td>
			<td>' . $row->vote_user_ip . '</td>
			</tr>';
		}

		echo '</table><br /><br /><br />';

		return;
	}
}
