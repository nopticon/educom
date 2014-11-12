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
class __ms extends common
{
	var $_no = true;
	var $methods = array(
		'login' => array('confirm'),
		'logout' => array(),
		'change' => array(),
		'force' => array(),
		'pwd' => array('act')
	);
	var $al = array('login' => true, 'logout' => true);
	var $wra = true;
	
	function home()
	{
		global $nucleo;
		
		$nucleo->redirect($nucleo->link());
	}
	
	function login()
	{
		$this->method();
	}
	
	function _login_home()
	{
		global $user, $db, $nucleo;
		
		$this->__is_post();
		
		$v = $this->control->__(array('lastpage', 'address', 'password'));
		
		if ($user->data['is_member']) {
			$nucleo->redirect($v['lastpage']);
		}
		
		if (empty($v['address']) || empty($v['key']))
		{
			$this->error('LOGIN_ERROR');
		}
		
		if (!$this->errors() && email_format($v['address']) === false)
		{
			$this->error('LOGIN_ERROR');
		}
		
		if (!$this->errors())
		{
			$sql = "SELECT *
				FROM _members
				WHERE user_email = '" . $db->sql_escape($v['address']) . "'
					AND user_id <> " . GUEST . '
					AND user_inactive = 0';
			
			$is_register = true;
			if ($userdata = $this->_fieldrow($sql))
			{
				$is_register = false;
				
				if ($userdata['user_password'] === $nucleo->password($v['key']))
				{
					$user->session_create($userdata['user_id']);
					$nucleo->redirect($v['lastpage']);
				}
				
				// TODO: Limit login tries
				$this->error('LOGIN_ERROR');
			}
			
			if ($is_register)
			{
				$v = array_merge($v, $this->control->__(array('invite', 'ref', 'ref_in')));
				
				// Invite
				if (!empty($v['invite']))
				{
					$sql = "SELECT i.invite_email, m.user_email
						FROM _members_ref_invite i, _members m
						WHERE i.invite_code = '" . $db->sql_escape($v['invite']) . "'
							AND i.invite_uid = m.user_id";
					if (!$row_invite = $this->_fieldrow($sql))
					{
						$nucleo->fatal();
					}
					
					$v['ref'] = 1;
					$v['ref_in'] = $row_invite['user_email'];
					$v['address'] = $row_invite['invite_email'];
				}
				
				if ($this->button())
				{
					$v = array_merge($v, $this->control->__(array('alias', 'username', 'gender', 'country', 'birth_day', 'birth_month', 'birth_year', 'aup')));
					
					if (empty($v['alias']) || empty($v['username']))
					{
						$this->error('E_REGISTER_EMPTY_USERNAME');
					}
					
					if (!$this->errors())
					{
						if (!preg_match('#^([a-z0-9\_\-]+)$#is', $v['alias']))
						{
							$this->error('E_REGISTER_BAD_ALIAS');
						}
					}
					
					if (!$this->errors())
					{
						$v['alias_len'] = strlen($v['alias']);
						if (($v['alias_len'] < 1) || ($v['alias_len'] > 20))
						{
							$this->error('E_REGISTER_LEN_ALIAS');
						}
						
						$v['username_len'] = strlen($v['username']);
						if (($v['username_len'] < 1) || ($v['username_len'] > 20))
						{
							$this->error('E_REGISTER_LEN_ALIAS');
						}
					}
					
					if (!$this->errors())
					{
						$sql = "SELECT *
							FROM _subdomains
							WHERE s_name = '" . $db->sql_escape($v['alias']) . "'";
						if ($this->_fieldrow($sql))
						{
							$this->error('E_REGISTER_RECORD_ALIAS');
						}
					}
					
					//
				}
				
				// GeoIP
				include(SROOT . 'core/geoip.php');
				$gi = geoip_open(SROOT . 'core/GeoIP.dat', GEOIP_STANDARD);
				
				$geoip_code = strtolower(geoip_country_code_by_addr($gi, $user->ip));
				
				$sql = 'SELECT *
					FROM _countries
					ORDER BY country_name';
				$countries = $this->_rowset($sql);
				
				$v2['country'] = ($v2['country']) ? $v2['country'] : ((isset($country_codes[$geoip_code])) ? $country_codes[$geoip_code] : $country_codes['gt']);
				
				foreach ($countries as $i => $row)
				{
					if (!$i)
					{
						$style->assign_block_vars('countries', array());
					}
					
					$style->assign_block_vars('countries.row', array(
						'V_ID' => $row['country_id'],
						'V_NAME' => $row['country_name'],
						'V_SEL' => 0
					));
				}
				
				$tv = array(
					'V_EMAIL' => $v['address'],
					'V_PASSWORD' => $v['key']
				);
			}
			else
			{
				$user->login('', $this->get_errors());
			}
		}
		
		return;
	}
	
	function _login_confirm()
	{
		global $user, $nucleo;
		
		return;
	}
	
	function logout()
	{
		global $user, $nucleo;
		
		if ($user->data['is_member'])
		{
			$user->session_kill();
			
			$user->data['is_member'] = false;
			$user->data['session_page'] = '';
			$user->data['session_time'] = $user->time;
			
			$style_vars = array(
				'CUSTOM_MESSAGE' => lang('logged_out'),
				'REDIRECT_TO' => $nucleo->link()
			);
			$nucleo->layout('login', 'LOGIN', $style_vars);
		}
		$nucleo->redirect($nucleo->link());
	}
	
	function change()
	{
		global $user, $nucleo;
		
		if ($user->data['is_member'])
		{
			$user->session_kill();
		}
		
		if ($this->button())
		{
			
		}
	}
	
	function pwd()
	{
		$this->method();
	}
	
	function _pwd_home()
	{
		
	}
	
	function _pwd_act()
	{
		
	}
}