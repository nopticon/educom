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

class __broadcast_modify extends mac {
	public function __construct() {
		parent::__construct();
		
		$this->auth('founder');
	}
	
	public function _home() {
		global $config, $user, $cache;
		
		$ftp = new ftp();
		
		if (!$ftp->ftp_connect($config->broadcast_host)) {
			_pre('Can not connect', true);
		}
		
		if (!$ftp->ftp_login($config->broadcast_username, $config->broadcast_password)) {
			$ftp->ftp_quit();
			_pre('Can not login', true);
		}
		
		$cds_file = ROOT . 'interfase/cds/schedule_playlist.txt';
		
		// Submit
		if (_button()) {
			$hours = request_var('hours', array('' => ''));
			
			$build = '';
			foreach ($hours as $hour => $play) {
				$build .= ((!empty($build)) ? nr(1) : '') . trim($hour) . ':' . trim($play);
			}
			
			if ($fp = @fopen($cds_file, 'w')) {
				@flock($fp, LOCK_EX);
				fwrite($fp, $build);
				@flock($fp, LOCK_UN);
				fclose($fp);
				
				_chmod($cds_file, $config->mask);
				
				if ($ftp->ftp_put('/Schedule/schedule_playlist.txt', $cds_file)) {
					echo '<h1>El archivo fue procesado correctamente.</h1>';
				} else {
					echo '<h1>Error al procesar, intenta nuevamente.</h1>';
				}
			} else {
				echo 'Error de escritura en archivo local.';
			}
			
			echo '<br />';
		}
		
		if (!@file_exists($cds_file)) {
			fatal_error();
		}
		
		$cds = @file($cds_file);
		
		$filelist = $ftp->ftp_nlist('/Schedule');
		echo '<pre>';
		print_r($filelist);
		echo '</pre>';
		
		foreach ($cds as $item) {
			$e_item = array_map('trim', explode(':', $item));
			if (!empty($e_item[0])) {
				echo sumhour($e_item[0]) . ' <input type="text" name="hours[' . $e_item[0] . ']" value="' . $e_item[1] . '" size="100"' . ((oclock($e_item[0])) ? 'class="highlight"' : '') . ' /><br />' . nr();
			}
		}
		
		$ftp->ftp_quit();
		
		return true;
	}
}