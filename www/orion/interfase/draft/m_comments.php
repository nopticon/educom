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

require_once(ROOT . 'interfase/comments.php');

class comments extends common {
	public $methods = array(
		'emoticons' => array('add', 'edit', 'delete'),
		'help' => array('add', 'edit', 'delete')
	);
	
	public function __construct() {
		return;
	}
	
	public function nav() {
		global $user;
		
		$this->control->set_nav(array('mode' => $this->mode), $user->lang['CONTROL_COMMENTS_' . strtoupper($this->mode)]);
	}
	
	public function home() {
		global $user;
		
		_style('menu');
		
		foreach ($this->methods as $module => $void) {
			_style('menu.item', array(
				'URL' => s_link_control('comments', array('mode' => $module)),
				'TITLE' => $user->lang['CONTROL_COMMENTS_' . strtoupper($module)])
			);
		}
		
		return;
	}
	
	//
	// Help
	//
	public function help() {
		$this->call_method();
	}
	
	public function _help_home() {
		global $user;
		
		$comments = new _comments();
		
		$ha = $this->auth->query('comments');
		
		if ($ha) {
			$ha_add = $this->auth->option(array('help', 'add'));
			$ha_edit = $this->auth->option(array('help', 'edit'));
			$ha_delete = $this->auth->option(array('help', 'delete'));
		}
		
		$sql = 'SELECT c.*, m.*
			FROM _help_cat c, _help_modules m
			WHERE c.help_module = m.module_id
			ORDER BY c.help_order';
		$cat = sql_rowset($sql, 'help_id');
		
		$sql = 'SELECT *
			FROM _help_faq';
		$faq = sql_rowset($sql, 'faq_id');
		
		//
		// Loop
		//
		foreach ($cat as $help_id => $cdata) {
			_style('cat', array(
				'HELP_ES' => $cdata['help_es'],
				'HELP_EN' => $cdata['help_en'],
				
				'HELP_EDIT' => s_link_control('comments', array('mode' => $this->mode)),
				'HELP_UP' => s_link_control('comments', array('mode' => $this->mode)),
				'HELP_DOWN' => s_link_control('comments', array('mode' => $this->mode)))
			);
			
			if ($ha_edit) {
				_style('cat.edit', array(
					'URL' => s_link_control('comments', array('mode' => $this->mode, 'manage' => 'edit', 'sub' => 'cat', 'id' => $help_id)),
					'UP' => s_link_control('comments', array('mode' => $this->mode, 'manage' => 'edit', 'sub' => 'cat', 'id' => $help_id, 'order' => '_15')),
					'DOWN' => s_link_control('comments', array('mode' => $this->mode, 'manage' => 'edit', 'sub' => 'cat', 'id' => $help_id, 'order' => '15')))
				);
			}
			
			if ($ha_delete) {
				_style('cat.delete', array(
					'URL' => s_link_control('comments', array('mode' => $this->mode, 'manage' => 'delete', 'sub' => 'cat', 'id' => $help_id)))
				);
			}
			
			foreach ($faq as $faq_id => $fdata) {
				if ($help_id != $fdata['help_id']) {
					continue;
				}
				
				_style('cat.faq', array(
					'QUESTION_ES' => $fdata['faq_question_es'],
					'ANSWER_ES' => $comments->parse_message($fdata['faq_answer_es']))
				);
				
				if ($ha_edit) {
					_style('cat.faq.edit', array(
						'URL' => s_link_control('comments', array('mode' => $this->mode, 'manage' => 'edit', 'sub' => 'faq', 'id' => $fdata['faq_id'])))
					);
				}
				
				if ($ha_delete) {
					_style('cat.faq.delete', array(
						'URL' => s_link_control('comments', array('mode' => $this->mode, 'manage' => 'delete', 'sub' => 'faq', 'id' => $fdata['faq_id'])))
					);
				}
			}
		}
		
		if ($ha_add) {
			_style('add', array(
				'URL' => s_link_control('comments', array('mode' => $this->mode, 'manage' => 'add')))
			);
		}
		
		$this->nav();
		
		return;
	}
	
	public function _help_add() {
		global $user, $cache;
		
		$error = array();
		$sub = $this->control->get_var('sub', '');
		$submit = _button();
		
		$menu = array('module' => 'CONTROL_COMMENTS_HELP_MODULE', 'cat' => 'CATEGORY', 'faq' => 'FAQ');
		
		switch ($sub) {
			case 'cat':
				$module_id = 0;
				$help_es = '';
				$help_en = '';
				break;
			case 'faq':
				$help_id = 0;
				$question_es = '';
				$question_en = '';
				$answer_es = '';
				$answer_en = '';
				break;
			case 'module':
				$module_name = '';
				break;
			default:
				_style('menu');
				
				foreach ($menu as $url => $name) {
					_style('menu.item', array(
						'URL' => s_link_control('comments', array('mode' => $this->mode, 'manage' => $this->manage, 'sub' => $url)),
						'TITLE' => (isset($user->lang[$name])) ? $user->lang[$name] : $name)
					);
				}
				break;
		}
		
		if ($submit) {
			switch ($sub) {
				case 'cat':
					$module_id = $this->control->get_var('module_id', 0);
					$help_es = $this->control->get_var('help_es', '');
					$help_en = $this->control->get_var('help_en', '');
					
					if (empty($help_es) || empty($help_en)) {
						$error[] = 'CONTROL_COMMENTS_HELP_EMPTY';
					}
					
					// Insert
					if (!count($error)) {
						$sql_insert = array(
							'help_module' => (int) $module_id,
							'help_es' => $help_es,
							'help_en' => $help_en
						);
						
						$sql = 'INSERT INTO _help_cat' . sql_build('INSERT', $sql_insert);
					}
					break;
				case 'faq':
					$help_id = $this->control->get_var('help_id', 0);
					$question_es = $this->control->get_var('question_es', '');
					$question_en = $this->control->get_var('question_en', '');
					$answer_es = $this->control->get_var('answer_es', '');
					$answer_en = $this->control->get_var('answer_en', '');
					
					if (empty($question_es) || empty($question_en) || empty($answer_es) || empty($answer_en)) {
						$error[] = 'CONTROL_COMMENTS_HELP_EMPTY';
					}
					
					if (!count($error)) {
						$sql_insert = array(
							'help_id' => $help_id,
							'faq_question_es' => $question_es,
							'faq_question_en' => $question_en,
							'faq_answer_es' => $answer_es,
							'faq_answer_en' => $answer_en
						);
						$sql = 'INSERT INTO _help_faq' . sql_build('INSERT', $sql_insert);
					}
					break;
				case 'module':
					$module_name = $this->control->get_var('module_name', '');
					
					if (empty($module_name)) {
						$error[] = 'CONTROL_COMMENTS_HELP_EMPTY';
					}
					
					if (!count($error)) {
						$sql_insert = array(
							'module_name' => $module_name
						);
						$sql = 'INSERT INTO _help_modules' . sql_build('INSERT', $sql_insert);
					}
					break;
			}
			
			if (!count($error)) {
				sql_query($sql);
				
				$cache->delete('help_cat help_faq help_modules');
				
				redirect(s_link_control('comments', array('mode' => $this->mode)));
			} else {
				_style('error', array(
					'MESSAGE' => parse_error($error))
				);
			}
		}
		
		$this->nav();
		$this->control->set_nav(array('mode' => $this->mode, 'manage' => $this->manage), 'CONTROL_ADD');
		$this->control->set_nav(array('mode' => $this->mode, 'manage' => $this->manage, 'sub' => $sub), (isset($user->lang[$menu[$sub]])) ? $user->lang[$menu[$sub]] : $menu[$sub]);
		
		$layout_vars = array(
			'SUB' => $sub,
			'S_HIDDEN' => s_hidden(array('module' => $this->control->module, 'mode' => $this->mode, 'manage' => $this->manage, 'sub' => $sub))
		);
		
		switch ($sub) {
			case 'cat':
				$sql = 'SELECT *
					FROM _help_modules
					ORDER BY module_id';
				$result = sql_rowset($sql);
				
				$select_mod = '';
				foreach ($result as $row) {
					$selected = ($row['module_id'] == $module_id);
					$select_mod .= '<option' . (($selected) ? ' class="bold"' : '') . ' value="' . $row['module_id'] . '"' . (($selected) ? ' selected' : '') . '>' . $row['module_name'] . '</option>';
				}
				
				$layout_vars += array(
					'MODULE' => $select_mod,
					'HELP_ES' => $help_es,
					'HELP_EN' => $help_en
				);
				break;
			case 'faq':
				$sql = 'SELECT *
					FROM _help_cat
					ORDER BY help_id';
				$result = sql_rowset($sql);
				
				$select_cat = '';
				foreach ($result as $row) {
					$selected = ($row['help_id'] == $help_id);
					$select_cat .= '<option' . (($selected) ? ' class="bold"' : '') . ' value="' . $row['help_id'] . '"' . (($selected) ? ' selected' : '') . '>' . $row['help_es'] . ' | ' . $row['help_en'] . '</option>';
				}
				
				$layout_vars += array(
					'CATEGORY' => $select_cat,
					'QUESTION_ES' => $question_es,
					'QUESTION_EN' => $question_en,
					'ANSWER_ES' => $answer_es,
					'ANSWER_EN' => $answer_en
				);
				break;
			case 'module':
				$layout_vars += array(
					'MODULE_NAME' => $module_name
				);
				break;
		}
		
		return v_style($layout_vars);
	}
	
	public function _help_edit_move() {
		$sql = 'SELECT *
			FROM _help_cat
			ORDER BY help_order';
		$result = sql_rowset($sql);
		
		$i = 10;
		foreach ($result as $row) {
			$sql = 'UPDATE _help_cat SET help_order = ?
				WHERE help_id = ?';
			sql_query(sql_filter($sql, $i, $row['help_id']));
			
			$i += 10;
		}
		
		return;
	}
	
	public function _help_edit() {
		global $user, $cache;
		
		$error = array();
		$sub = $this->control->get_var('sub', '');
		$id = $this->control->get_var('id', 0);
		$submit = _button();
		
		switch ($sub) {
			case 'cat':
				$sql = 'SELECT c.*, m.*
					FROM _help_cat c, _help_modules m
					WHERE c.help_id = ?
						AND c.help_module = m.module_id';
				if (!$cat_data = sql_fieldrow(sql_filter($sql, $id))) {
					fatal_error();
				}
				
				$order = $this->control->get_var('order', '');
				if (!empty($order)) {
					if (preg_match('/_(\d+)/', $order)) {
						$sig = '-';
						$order = str_replace('_', '', $order);
					} else {
						$sig = '+';
					}
					
					$sql = 'UPDATE _help_cat SET help_order = help_order ?? ??
						WHERE help_id = ?';
					sql_query(sql_filter($sql, $sig, $order, $id));
					
					$this->_help_edit_move();
					
					$cache->delete('help_cat');
					
					redirect(s_link_control('comments', array('mode' => $this->mode)));
				} // IF order
				
				$module_id = $cat_data['help_module'];
				$help_es = $cat_data['help_es'];
				$help_en = $cat_data['help_en'];
				break;
			case 'faq':
				$sql = 'SELECT *
					FROM _help_faq
					WHERE faq_id = ?';
				if (!$faq_data = sql_fieldrow(sql_filter($sql, $id))) {
					fatal_error();
				}
				
				$question_es = $faq_data['faq_question_es'];
				$question_en = $faq_data['faq_question_en'];
				$answer_es = $faq_data['faq_answer_es'];
				$answer_en = $faq_data['faq_answer_en'];
				$help_id = $faq_data['help_id'];
				break;
			default:
				redirect(s_link_control('comments', array('mode' => $this->mode)));
				break;
		}
		
		// IF submit
		if ($submit) {
			switch ($sub) {
				case 'cat':
					$module_id = $this->control->get_var('module_id', 0);
					$help_es = $this->control->get_var('help_es', '');
					$help_en = $this->control->get_var('help_en', '');
					
					if (empty($help_es) || empty($help_en)) {
						$error[] = 'CONTROL_COMMENTS_HELP_EMPTY';
					}
					
					// Update
					if (!count($error)) {
						$sql_update = array(
							'help_es' => $help_es,
							'help_en' => $help_en,
							'help_module' => (int) $module_id
						);
						
						$sql = 'UPDATE _help_cat SET ??
							WHERE help_id = ?';
						sql_query(sql_filter($sql, sql_build('UPDATE', $sql_update), $id));
						
						$cache->delete('help_cat');
						
						redirect(s_link_control('comments', array('mode' => $this->mode)));
					}
					break;
				case 'faq':
					$question_es = $this->control->get_var('question_es', '');
					$question_en = $this->control->get_var('question_en', '');
					$answer_es = $this->control->get_var('answer_es', '');
					$answer_en = $this->control->get_var('answer_en', '');
					$help_id = $this->control->get_var('help_id', 0);
					
					if (empty($question_es) || empty($question_en) || empty($answer_es) || empty($answer_en)) {
						$error[] = 'CONTROL_COMMENTS_HELP_EMPTY';
					}
					
					if (!count($error)) {
						$sql = 'SELECT *
							FROM _help_cat
							WHERE help_id = ?';
						if (!$cat_data = sql_fieldrow(sql_filter($sql, $help_id))) {
							$error[] = 'CONTROL_COMMENTS_HELP_NOCAT';
						}
					}
					
					// Update
					if (!count($error)) {
						$sql_update = array(
							'help_id' => (int) $help_id,
							'faq_question_es' => $question_es,
							'faq_question_en' => $question_en,
							'faq_answer_es' => $answer_es,
							'faq_answer_en' => $answer_en
						);
						
						$sql = 'UPDATE _help_faq SET ??
							WHERE faq_id = ?';
						sql_query(sql_filter($sql, sql_build('UPDATE', $sql_update), $id));
						
						$cache->delete('help_faq');
						
						redirect(s_link_control('comments', array('mode' => $this->mode)));
					}
					break;
			} // switch
			
			if (count($error)) {
				_style('error', array(
					'MESSAGE' => parse_error($error))
				);
			}
		}
		
		$this->nav();
		$this->control->set_nav(array('mode' => $this->mode, 'manage' => $this->manage, 'sub' => $sub, 'id' => $id), 'CONTROL_EDIT');
		
		$layout_vars = array(
			'SUB' => $sub,
			'S_HIDDEN' => s_hidden(array('module' => $this->control->module, 'mode' => $this->mode, 'manage' => $this->manage, 'sub' => $sub, 'id' => $id))
		);
		
		switch ($sub) {
			case 'cat':
				$sql = 'SELECT *
					FROM _help_modules
					ORDER BY module_id';
				$result = sql_rowset($sql);
				
				$select_mod = '';
				foreach ($result as $row) {
					$selected = ($row['module_id'] == $module_id);
					$select_mod .= '<option' . (($selected) ? ' class="bold"' : '') . ' value="' . $row['module_id'] . '"' . (($selected) ? ' selected' : '') . '>' . $row['module_name'] . '</option>';
				}
				
				$layout_vars += array(
					'MODULE' => $select_mod,
					'HELP_ES' => $help_es,
					'HELP_EN' => $help_en
				);
				break;
			case 'faq':
				$sql = 'SELECT *
					FROM _help_cat
					ORDER BY help_id';
				$result = sql_rowset($sql);
				
				$select_cat = '';
				foreach ($result as $row) {
					$selected = ($row['help_id'] == $help_id);
					$select_cat .= '<option' . (($selected) ? ' class="bold"' : '') . ' value="' . $row['help_id'] . '"' . (($selected) ? ' selected' : '') . '>' . $row['help_es'] . ' | ' . $row['help_en'] . '</option>';
				}
				
				$layout_vars += array(
					'CATEGORY' => $select_cat,
					'QUESTION_ES' => $question_es,
					'QUESTION_EN' => $question_en,
					'ANSWER_ES' => $answer_es,
					'ANSWER_EN' => $answer_en
				);
				break;
		}
		
		_style($layout_vars);
		
		return;
	}
	
	public function _help_delete() {
		
	}
}