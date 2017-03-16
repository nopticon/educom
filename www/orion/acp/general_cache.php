<?php

if (!defined('IN_APP')) exit;

class __general_cache extends mac {
	public function __construct() {
		parent::__construct();

		$this->auth('founder');
	}

	public function _home() {
		global $config, $user, $cache;

		$list = w();
		foreach (array_dir($config->cache_path) as $row) {
			if (preg_match('/(.*?)\.php$/i', $row, $part)) $list[] = $part[1];
		}

		if ($list) {
			$cache->delete(implode(' ', $list));
		}

		return _pre('All cache was removed.', true);
	}
}
