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
require_once(ROOT . 'interfase/chat.php');

$user->init();
$user->setup('chat');

if (!$user->data['is_member']) {
	do_login('LOGIN_TO_CHAT');
}

$mode = request_var('mode', '');
$submit = _button();
$error = w();

switch ($mode) {
	case 'create':
		$template_vars = w();
		
		$ch_auth = 0;
		$ch_type = 1;
		$ch_cat = 2;
		
		if ($submit) {
			$ch_name = request_var('ch_name', '');
			$ch_desc = request_var('ch_desc', '');
			$ch_auth = request_var('ch_auth', 0);
			
			if ($user->data['is_founder']) {
				$ch_type = request_var('ch_type', 0);
				$ch_cat = request_var('ch_cat', 1);
			}
			
			if (empty($ch_name)) {
				$error[] = 'CHAT_CREATE_EMPTY';
			} else if (!preg_match('#^([a-z0-9\-]+)$#is', $ch_name)) {
				$error[] = 'CHAT_CREATE_INVALID_NAME';
			}
			
			$ch_int_name = strtolower($ch_name);
			
			if (!count($error)) {
				$sql = 'SELECT *
					FROM _chat_ch
					WHERE ch_int_name = ?
						OR ch_name = ?';
				if (sql_fieldrow(sql_filter($sql, $ch_int_name, $ch_name))) {
					$error[] = 'CHAT_ALREADY_CREATED';
				}
			}
			
			if (empty($ch_desc)) {
				$error[] = 'CHAT_CREATE_EMPTY_DESC';
			}
			
			if (!count($error)) {
				$insert_data = array(
					'cat_id' => (int) $ch_cat,
					'ch_int_name' => $ch_int_name,
					'ch_name' => $ch_name,
					'ch_desc' => $ch_desc,
					'ch_founder' => (int) $user->d('user_id'),
					'ch_type' => (int) $ch_type,
					'ch_auth' => (int) $ch_auth,
					'ch_users' => 0,
					'ch_def' => 0,
					'ch_ip' => $user->ip,
					'ch_locked' => 0
				);
				sql_insert('chat_ch', $insert_data);
				
				redirect(s_link('chat', $ch_int_name));
			}

			$template_vars += array(
				'CH_NAME' => $ch_name,
				'CH_DESC' => $ch_desc
			);
		} // IF $submit
		
		if ($user->data['is_founder']) {
			$chat = new _chat();
			
			if ($cat = $chat->get_cats()) {
				$cat_list = '';
				foreach ($cat as $cat_data) {
					$cat_list .= '<option value="' . $cat_data['cat_id'] . '"' . (($cat_data['cat_id'] == $ch_cat) ? ' selected' : '') . '>' . $cat_data['cat_name'] . '</option>';
				}
				
				_style('select_cat', array(
					'CHAT_SELECT_CAT' => $cat_list)
				);
			} // IF $cat
			
			$type_ary = w('CHAT_CH_OFFICIAL CHAT_CH_ALL');
			$type_list = '';
			foreach ($type_ary as $i => $langkey) {
				$type_list .= '<option value="' . $i . '"' . (($i == $ch_type) ? ' selected' : '') . '>' . lang($langkey) . '</option>';
			}
			
			_style('select_type', array(
				'CHAT_SELECT_TYPE' => $type_list)
			);
		}
		
		$select_auth = '';
		$auth_ary = w('CHAT_CH_ALL FRIENDS');
		foreach ($auth_ary as $i => $langkey) {
			$select_auth .= '<option value="' . $i . '"' . (($i == $ch_auth) ? ' selected' : '') . '>' . lang($langkey) . '</option>';
		}
		
		$template_vars += array(
			'CHAT_SELECT_AUTH' => $select_auth,
			'S_ACTION' => s_link('chat-create')
		);
		
		if (count($error)) {
			_style('error', array(
				'MESSAGE' => parse_error($error))
			);
		}
		
		page_layout('CHAT_CREATE', 'chat_create', $template_vars);
		break;
}

redirect(s_link('chat'));