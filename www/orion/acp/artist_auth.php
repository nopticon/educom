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

class __artist_auth extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('artist');
	}
	
	/*
	Show all authorized users to manage artist information.
	*/
	public function _home() {
		global $config, $user, $cache, $comments;
		
		$this->_artist();
		
		if ((_button() && $this->create()) || ((_button('confirm') || _button('remove')) && $this->remove())) {
			return;
		}
		
		$sql = 'SELECT u.user_id, u.user_type, u.username, u.username_base, u.user_avatar
			FROM _artists_auth a, _members u
			WHERE a.ub = ?
				AND a.user_id = u.user_id
			ORDER BY u.username';
		if ($result = sql_rowset(sql_filter($sql, $this->object->ub))) {
			$total = count($result);

			foreach ($result as $i => $row) {
				if (!$i) _style('members');

				$prof = $comments->user_profile($row);

				$delete = ($total > 1 && $prof->user_id != $user->d('user_id')) || ($user->is('founder') && $prof->user_id != $user->d('user_id'));

				_style('members.row', array(
					'USER_ID' => $prof->user_id,
					'PROFILE' => $prof->profile,
					'USERNAME' => $prof->username,
					'AVATAR' => $prof->user_avatar,
					'DELETE' => $delete,
					'CHECK' => ($total == 1 && $unique))
				);
			}
		}

		return;
	}

	/*
	Authorize new user to manage artist information.
	*/
	private function create() {
		global $user;

		$v = _request(array('s_member' => ''));

		if (!$v->s_member) {
			_style('no_members', array(
				'MESSAGE' => lang('control_a_auth_add_nomatch'))
			);

			return;
		}

		$ignore = USER_INACTIVE;

		if (!$user->is('founder')) {
			$ignore .= ', ' . USER_FOUNDER;
		}

		$sql = 'SELECT user_id, user_type, username, username_base, user_avatar
			FROM _members
			WHERE username = ?
				AND user_type NOT IN (??)';
		if (!$member = sql_fieldrow(sql_filter($sql, $v->s_member, $ignore))) {
			_style('no_members', array(
				'MESSAGE' => lang('control_a_auth_add_nomatch'))
			);

			return;
		}

		$sql = 'SELECT user_id
			FROM _artists_auth
			WHERE ub = ?
				AND user_id = ?';
		if (sql_field(sql_filter($sql, $this->object->ub, $member->user_id), 'user_id', 0)) {
			_style('no_members', array(
				'MESSAGE' => lang('control_a_auth_add_nomatch'))
			);

			return;
		}

		/*
		Authorize the selected user to this artist.
		*/
		$sql_insert = array(
			'ub' => $this->object->ub,
			'user_id' => $member->user_id
		);
		sql_insert('artists_auth', $sql_insert);

		/*
		Update information about the user with new rank.
		*/
		$update = array(
			'user_type' => USER_ARTIST,
			'user_auth_control' => 1
		);

		if (!$member['user_rank']) {
			$update['user_rank'] = $config->default_a_rank;
		}

		$sql = 'UPDATE _members SET ??
			WHERE user_id = ?
				AND user_type NOT IN (' . USER_INACTIVE . ', ' . USER_FOUNDER . ')';
		sql_query(sql_filter($sql, sql_build('UPDATE', $update), $member->user_id));

		$sql = 'SELECT fan_id
			FROM _artists_fav
			WHERE ub = ?
				AND user_id = ?';
		if ($fan_id = sql_field(sql_filter($sql, $this->object->ub, $member->user_id), 'fan_id', 0)) {
			$sql = 'DELETE FROM _artists_fav
				WHERE fan_id = ?';
			sql_query(sql_filter($sql, $fan_id));
		}

		/*
		Back to auth home
		*/
		return redirect(s_link('acp', array('artist_auth', 'a' => $this->object->subdomain)));
	}
	
	/*
	Revoke permission to manage artist's information, for selected users.
	*/
	private function remove() {
		global $config, $user;

		$auth_url = s_link('acp', array('artist_auth', 'a' => $this->object->subdomain));

		if (_button('cancel')) {
			redirect($auth_url);
		}

		$submit = _button('remove');
		$confirm = _button('confirm');

		if ($submit || $confirm) {
			$result = request_var('s_members', array(0));

			if (count($result)) {
				$sql = 'SELECT m.user_id, m.username, m.user_rank
					FROM _artists_auth a, _members m
					WHERE a.ub = ?
						AND m.user_id IN (??)
						AND m.user_id <> ?
						AND m.user_type <> ??
						AND a.user_id = m.user_id
					ORDER BY m.user_id';
				$result = sql_rowset(sql_filter($sql, $this->object->ub, implode(',', $result), $user->data->user_id, USER_INACTIVE), 'user_id');
			}

			if (!$result) {
				redirect($auth_url);
			}
			
			// If Confirm button is pressed.
			
			if ($confirm) {
				foreach ($result as $row) {
					$update = w();
					$user_type = USER_ARTIST;

					$sql = 'SELECT COUNT(ub) AS total
						FROM _artists_auth
						WHERE user_id = ?';
					$total = sql_field(sql_filter($sql, $row->user_id), 'total', 0);
					
					if ($total == 1) {
						$update['user_auth_control'] = 0;

						$user_type = USER_NORMAL;
						if ($item['user_rank'] == $config->default_a_rank) {
							$update['user_rank'] = 0;
						}

						$sql = 'SELECT *
							FROM _artists_fav
							WHERE user_id = ?';
						if (sql_fieldrow(sql_filter($sql, $row->user_id))) {
							$user_type = USER_FAN;
						}

						$update['user_type'] = $user_type;

						$sql = 'UPDATE _members SET ??
							WHERE user_id = ?';
						sql_query(sql_filter($sql, sql_build('UPDATE', $update), $row->user_id));
					}

					$sql = 'DELETE FROM _artists_auth
						WHERE ub = ?
							AND user_id = ?';
					sql_query(sql_filter($sql, $this->object->ub, $row->user_id));
				}

				return redirect($auth_url);
			}

			// Display confirm dialog			
			$result_list = '';
			
			foreach ($result as $row) {
				$result_list .= (($result_list != '') ? ', ' : '') . $row->username;
				$result_hidden .= s_hidden(array('s_members[]' => $row->user_id));
			}

			$message = count($result) == 1 ? '2' : '';

			$layout_vars = array(
				'MESSAGE_TEXT' => sprintf(lang('acp_artist_auth_delete' . $message), $this->object->name, $result_list),
				'S_CONFIRM_ACTION' => s_link('acp', array('artist_auth', 'a' => $this->object->subdomain)),
				'S_HIDDEN_FIELDS' => $result_hidden
			);

			page_layout('ACP_ARTIST_AUTH', 'confirm', $layout_vars);
		}

		redirect($auth_url);
		
		return;
	}
}