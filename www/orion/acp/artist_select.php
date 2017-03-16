<?php

if (!defined('IN_APP')) exit;

class __artist_select extends mac {
	public function __construct() {
		parent::__construct();

		$this->auth('artist');
	}

	public function _home() {
		global $config, $user, $cache;

		$artist = request_var('a', '');
		$redirect = request_var('r', '');

		if (!empty($artist)) {
			redirect(s_link('acp', array($redirect, 'a' => $artist)));
		}

		$artist_select = '';
		if (!$user->is('founder')) {
			$sql = 'SELECT ub
				FROM _artists_auth
				WHERE user_id = ?';
			$artist_select = ' WHERE ub IN (' . _implode(',', sql_rowset(sql_filter($sql, $user->d('user_id')), false, 'ub')) . ') ';
		}

		$sql = 'SELECT ub, subdomain, name
			FROM _artists
			??
			ORDER BY name';
		$artists = sql_rowset(sql_filter($sql, $artist_select));

		foreach ($artists as $i => $row) {
			if (!$i) _style('artist_list');

			_style('artist_list.row', array(
				'URL' => s_link('acp', array($redirect, 'a' => $row->subdomain)),
				'NAME' => $row->name)
			);
		}

		return;
	}
}
