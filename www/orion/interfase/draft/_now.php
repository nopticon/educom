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
class __now extends common {
	var $_no = true;
	var $methods = array(
		'listeners' => array()
	);
	
	function home() {
		global $nucleo;
		
		$v = $this->control->__(array('a' => array('default' => 0)));
		if (!$v['a']) {
			$nucleo->redirect($nucleo->link('radio'));
		}
		
		// Open Connection
		$connect_param = 'GET http://' . $nucleo->config['sc_stats_host'] . '/shoutcast/text/index.php?';
		$connect_param.= 'server=' . $nucleo->config['sc_stats_ip'] . '&port=' . $nucleo->config['sc_stats_ipport'] . " HTTP/1.0\r\n";
		$connect_param.= "User-Agent: StreamSolutions  (Mozilla Compatible)\r\n\r\n";
		
		$connect_recv = $this->sock($nucleo->config['sc_stats_host'], $connect_param, $nucleo->config['sc_stats_port']);
		if (!$connect_recv) {
			$this->e('RADIO_UNAVAILABLE');
		}
		
		//
		$response = array();
		$lines = array_slice(split("\n", trim($connect_recv)), 8);
		foreach ($lines as $line) {
			$e = explode('<SSTAG>', $line);
			$response[$e[0]] = $e[1];
		}
		
		if ($response['server_status'] == $nucleo->config['sc_stats_down']) {
			$this->e('RADIO_UNAVAILABLE');
		}
		
		// Parse song
		$song = array_map('trim', explode('-', $response['current_song']));
		$song[1] = array_pop($song);
		
		$result = '';
		if (!empty($response['stream_title']) && $response['stream_title'] != $config['sitename']) {
			$result .= '<div class="live">Al aire</div><div class="livetitle"><span>' . $response['stream_title'] . '</span></div>';
		}
		
		foreach ($song as $row) {
			$result .= '<div>' . $row . '</div>';
		}
		
		$this->e($result);
		
		return;
	}
	
	function listeners() {
		global $nucleo, $user;
		
		if (!$user->data['is_founder']) {
			$nucleo->redirect($nucleo->link('radio'));
		}
		
		// Open Connection
		$connect_param = 'GET http://' . $nucleo->config['sc_stats_host'] . '/shoutcast/text/index.php?';
		$connect_param.= 'server=' . $nucleo->config['sc_stats_ip'] . '&port=' . $nucleo->config['sc_stats_ipport'] . " HTTP/1.0\r\n";
		$connect_param.= "User-Agent: StreamSolutions  (Mozilla Compatible)\r\n\r\n";
		
		$connect_recv = $this->sock($nucleo->config['sc_stats_host'], $connect_param, $nucleo->config['sc_stats_port']);
		if (!$connect_recv) {
			$this->e('RADIO_UNAVAILABLE');
		}
		
		//
		$response = array();
		$lines = array_slice(split("\n", trim($connect_recv)), 8);
		foreach ($lines as $line) {
			$e = explode('<SSTAG>', $line);
			$response[$e[0]] = $e[1];
		}
		
		return $this->e($response);
	}
}