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

class ftp {
	private $conn_id;
	private $def = array();
	
	public function __construct() {
		global $config;
		
		// Decode file
		if (@file_exists(ROOT . '.htfda') && $a = @file(ROOT . '.htfda')) {
			// server.user.pwd.folder
			$d = explode(',', _decode($a[0]));
			foreach (w('server user passwd folder') as $i => $row) {
				$this->def[$row] = _decode($d[$i]);
			}
		}
		
		return;
	}
	
	public function ftp_connect($host = false, $port = false, $timeout = 10) {
		$host = ($host !== false) ? $host : $this->def['server'];
		$port = ($port !== false) ? $port : 21;
		
		$this->conn_id = ftp_connect($host, $port, $timeout);
		return $this->conn_id;
	}
	
	public function ftp_login($ftp_user = false, $ftp_pass = false) {
		$ftp_user = ($ftp_user !== false) ? $ftp_user : $this->def['user'];
		$ftp_pass = ($ftp_pass !== false) ? $ftp_pass : $this->def['passwd'];
		
		return @ftp_login($this->conn_id, $ftp_user, $ftp_pass);
	}
	
	public function dfolder() {
		return $this->def['folder'];
	}
	
	public function ftp_quit() {
		if ($this->conn_id) {
			@ftp_close($this->conn_id);
		}
		
		return;
	}
	
	public function ftp_pwd() {
		return @ftp_pwd($this->conn_id);
	}
	
	public function ftp_nlist($d = './') {
		return @ftp_nlist($this->conn_id, $d);
	}
	
	public function ftp_chdir($ftp_dir) {
		return @ftp_chdir($this->conn_id, $ftp_dir);
	}
	
	public function ftp_mkdir($ftp_dir) {
		return @ftp_mkdir($this->conn_id, $ftp_dir);
	}
	
	public function ftp_site($cmd) {
		return @ftp_site($this->conn_id, $cmd);
	}
	
	public function ftp_cdup() {
		return @ftp_cdup($this->conn_id);
	}
	
	public function ftp_put($remote_file, $local_file) {
		if (!file_exists($local_file)) {
			return false;
		}
		
		return @ftp_put($this->conn_id, $remote_file, $local_file, FTP_BINARY);
	}
	
	public function ftp_rename($src, $dest) {
		return @ftp_rename($this->conn_id, $src, $dest);
	}
}