<?php

if (!defined('IN_APP')) exit;

class __forums_posts_space extends mac {
	public function __construct() {
		parent::__construct();

		$this->auth('founder');
	}

	public function _home() {
		global $config, $user, $cache;

		$sql = 'SELECT *
			FROM _forum_posts
			WHERE post_id = 125750';
		if ($row = sql_fieldrow($sql)) {
			$a_post = str_replace("\r", '', $row['post_text']);

			$sql = 'UPDATE _forum_posts SET post_text = ?
				WHERE post_id = ?';
			sql_query(sql_filter($sql, $a_post, $row['post_id']));
		}

		return;
	}
}

?>
