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

$keepalive = true;

$user->init();
$chat = new _chat();

if ($chat->_setup()) {
	$mode = request_var('mode', '');
	$csid = request_var('csid', '');
	
	$s_process = in_array($mode, array('logout', 'send', 'get'));
	
	if (request_method() == 'post' && !$s_process) {
		redirect(s_link('chat', $chat->data['ch_int_name']));
	}
	
	if (!$user->data['is_member']) {
		do_login('LOGIN_TO_CHAT');
	}
	
	if (!$chat->auth()) {
		trigger_error('CHAT_NO_ACCESS');
	}
	
	$user->setup('chat');
	
	if ($s_process && $mode == 'logout') {
		return $chat->process_data($csid, $mode);
	}
	
	$chat->session($csid);
	
	if ($s_process) {
		return $chat->process_data($csid, $mode);
	}
	
	//$chat->sys_clean();
	$chat->window();
	
	$keepalive = false;
	$htmlpage = 'chat_channel';
	$page_title = lang('chat') . ' | ' . $chat->data['ch_name'];
} else {
	$cat = $chat->get_cats();
	
	if (!count($cat)) {
		trigger_error('NO_CHAT_CATS');
	}
	
	$user->setup('chat');
	
	$chat->sys_clean();
	$chatters = $chat->get_ch_listing($cat);
	
	v_style(array(
		'CHATTERS' => $chatters,
		'CREATE_CHAT' => s_link('chat-create'))
	);
	
	//
	// SET TEMPLATE
	$page_title = 'CHAT';
	$htmlpage = 'chat_body';
}

page_layout($page_title, $htmlpage, false, $keepalive);