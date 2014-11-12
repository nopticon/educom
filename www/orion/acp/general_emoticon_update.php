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

class __general_emoticon_update extends mac {
	public function __construct() {
		parent::__construct();

		$this->auth('founder');
	}

	public function _home() {
		global $config, $user, $cache;

		sql_truncate('_smilies');

		$emoticon_path = $config->assets_path . 'emoticon/';
		$process = 0;

		$fp = @opendir($emoticon_path);
		while ($file = @readdir($fp)) {
			if (preg_match('#([a-z0-9]+)\.(gif|png)#is', $file, $part)) {
				$insert = array(
					'code' => ':' . $part[1] . ':',
					'smile_url' => $part[0]
				);
				sql_insert('smilies', $insert);

				$process++;
			}
		}
		@closedir($fp);

		$cache->delete('smilies');

		return _pre($process . ' emoticons.');
	}
}