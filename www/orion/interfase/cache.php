<?php
/*
<Orion, a web development framework for RK.>
Copyright (C) <2011>  <Orion>

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.
*/
if (!defined('IN_APP')) exit;

class cache {
	public $cache = array();
	public $use = true;

	public function __construct() {
		if (!defined('USE_CACHE')) {
			$this->use = false;
		}
	}

	public function config() {
		$sql = 'SELECT *
			FROM _application';
		$config = sql_rowset($sql, 'config_name', 'config_value');

		return $config;
	}

	public function get($var) {
		if (!$this->use) {
			return false;
		}

		global $config;

		$filename = $config->cache_path . $var . '.php';

		if (@file_exists($filename)) {
			if (!@require_once($filename)) {
				$this->delete($var);
				return;
			}

			if (!empty($this->cache[$var])) {
				return json_decode($this->cache[$var]);
			}

			return true;
		}

		return;
	}

	public function save($var, &$data) {
		global $config;

		if (!$this->use) {
			return;
		}

		$filename = $config->cache_path . $var . '.php';

		if ($fp = @fopen($filename, 'w')) {
			$file_buffer = '<?php $' . "this->cache['" . $var . "'] = '" . json_encode($data) . "';";

			@flock($fp, LOCK_EX);
			@fwrite($fp, $file_buffer);
			@flock($fp, LOCK_UN);
			@fclose($fp);

			_chmod($filename, $config->mask);
		}

		return $data;
	}

	public function delete($list) {
		global $config;

		if (!$this->use) {
			return;
		}

		foreach (w($list) as $var) {
			$cache_filename = $config->cache_path . $var . '.php';
			if (file_exists($cache_filename)) {
				_rm($cache_filename);
			}
		}

		return;
	}
}