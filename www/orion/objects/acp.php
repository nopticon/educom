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
define('IN_APP', true);
require_once('./interfase/common.php');

class mac {
	public $_access;
	public $url;
	public $tv = array();
	public $warning_ls = array();

	protected $object;

	public function __construct() {
		return;
	}

	public function warning($message) {
		$this->warning_ls['warning'][] = lang($message);
		return false;
	}

	public function success($message) {
		$this->warning_ls['success'][] = lang($message);
		return false;
	}

	public function warning_show() {
		if ($this->warning_ls) {
			_style('alert');

			ksort($this->warning_ls);

			foreach($this->warning_ls as $type => $row) {
				_style('alert.row', array(
					'MESSAGE' => implode('<br />', $row),
					'CLASS' => $type)
				);
			}

			$this->warning_ls = w();
			return false;
		}

		return true;
	}

	public function auth($name) {
		global $user;

		if (!$user->is($name)) {
			if (defined('_ACP')) {
				$this->_access = false;

				return false;
			}
			return fatal_error();
		}

		$this->_access = true;
		return true;
	}

	public function can() {
		return $this->_access;
	}

	public function _artist() {
		global $user;

		if ($user->is('artist')) {
			$sql = 'SELECT a.ub
				FROM _artists_auth t
				INNER JOIN _artists a ON a.ub = t.ub
				WHERE t.user_id = ?';
			if ($artist_ary = sql_rowset(sql_filter($sql, $user->d('user_id')), false, 'ub')) {
				$sql_where = sql_filter('WHERE ub IN (??)', implode(',', $artist_ary));
			}
		}

		$artist = request_var('a', '');
		$module = request_var('module', '');
		$url = s_link('acp', array('artist_select', 'r' => $module));

		if (empty($artist)) {
			redirect($url);
		}

		if (!$this->object = get_artist($artist, true)) {
			fatal_error();
		}

		v_style(array(
			'ARTIST_SELECT' => $url,
			'ARTIST_NAME' => $this->object->name)
		);

		return;
	}
}

class acp {
	private $module;

	public function __construct() {
		global $user;

		if (!$user->is('member')) {
			do_login();
		}

		if ($arg = request_var('args', '')) {
			foreach (explode('.', $arg) as $str_pair) {
				$pair = explode(':', $str_pair);

				if (isset($pair[0]) && isset($pair[1]) && !empty($pair[0])) {
					$_REQUEST[$pair[0]] = $pair[1];
				}
			}
		}

		return;
	}

	public function get_title($default = '') {
		return (!empty($this->_title)) ? $this->_title : $default;
	}

	public function get_template($default = '') {
		return (!empty($this->_template)) ? $this->_template : $default;
	}

	public function run() {
		$this->module = request_var('module', '');

		if (empty($this->module)) {
			return $this->rights();
		}

		if (!preg_match('#[a-z\_]+#i', $this->module)) {
			fatal_error();
		}

		$this->filepath = ROOT . 'acp/' . $this->module . '.php';

		if (!@file_exists($this->filepath)) {
			fatal_error();
		}

		require_once($this->filepath);

		$_object = '__' . $this->module;
		if (!class_exists($_object)) {
			fatal_error();
		}

		$module = new $_object();

		$module->url = s_link() . substr(v_server('REQUEST_URI'), 1);
		$module->alias = $this->module;

		$module->_home();

		if (!isset($module->template)) {
			$module->template = 'acp/' . $this->module;
		}

		$local_tv = array(
			'MODULE_URL' => $module->url
		);

		if (isset($module->tv)) {
			$local_tv = array_merge($local_tv, $module->tv);
		}

		$this->_title = $this->module;
		$this->_template = $module->template;

		return v_style($local_tv);
	}

	private function rights() {
		$acp_dir = ROOT . 'acp/';

		$list = w();

		$fp = @opendir($acp_dir);
		while ($row = @readdir($fp)) {
			if (!preg_match('#([a-z\_]+).php#i', $row, $part) || $row == '_template.php') {
				continue;
			}

			require_once($acp_dir . $row);

			$acp_alias = $part[1];
			$object_name = '__' . $acp_alias;

			if (!class_exists($object_name)) {
				continue;
			}

			if (!defined('_ACP')) {
				define('_ACP', true);
			}

			$object = new $object_name();

			if ($object->can()) {
				switch ($acp_alias) {
					case 'artist_select':
						continue 2;
					break;
				}

				$list[] = $acp_alias;
			}
		}
		@closedir($fp);

		$group = w();
		foreach ($list as $row) {
			$part = explode('_', $row);

			if (!isset($group[$part[0]])) $group[$part[0]] = w();

			$group[$part[0]][] = $row;
		}

		// array_multisort($group);
		ksort($group);

		$i = 0;
		foreach ($group as $name => $list) {
			if (!$i) _style('acp_list');

			sort($row);

			_style('acp_list.group', array(
				'TITLE' => lang('acp_list_' . $name, $name)
			));

			foreach ($list as $row) {
				_style('acp_list.group.row', array(
					'URL' => s_link('acp', $row),
					'NAME' => lang('ACP_' . $row, $row),
					'IMAGE' => $row)
				);
			}
		}

		$board = new board();
		$cat = $board->categories();
		$forums = $board->forums();
		// _pre($board->forum_data, true);
		$board->forum_special();

		return;
	}
}