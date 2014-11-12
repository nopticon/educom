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

if (class_exists('_rss')) {
	return;
}

class rss {
	private $xml = array();
	private $mode;
	
	function __construct() {
		return;
	}
	
	function smode($mode) {
		$this->mode = $mode;
	}
	
	function _news() {
		$sql = 'SELECT n.*, m.username
			FROM _news n, _members m
			WHERE n.poster_id = m.user_id
			ORDER BY post_time DESC
			LIMIT 15';
		$result = sql_rowset($sql);
		
		foreach ($result as $row) {
			$this->xml[] = array(
				'title' => $row->post_subject,
				'link' => s_link('news', $row->news_id),
				'description' => $row->post_desc,
				'pubdate' => $row->post_time,
				'author' => $row->username
			);
		}
		
		return;
	}
	
	function _events() {
		return;
	}
	
	function _artists() {
		$sql = 'SELECT name, subdomain, genre, datetime, local, location
			FROM _artists
			ORDER BY datetime DESC
			LIMIT 15';
		$result = sql_rowset($sql);
		
		foreach ($result as $row) {
			$this->xml[] = array(
				'title' => $row->name,
				'link' => s_link('a', $row->subdomain),
				'description' => ($row->genre . "<br />" . (($row->local) ? 'Guatemala' : $row->location)),
				'pubdate' => $row->datetime
			);
		}
		
		return;
	}
	
	function output() {
		global $user;

		$each_format = "\t" . '<item>%s<title><![CDATA[%s]]></title><link>%s</link><guid>%s</guid><description><![CDATA[%s]]></description><pubDate>%s</pubDate></item>' . nr();
		$full_format = '<?xml version="1.0" encoding="utf-8"?><rss version="2.0"><channel><title>%s</title><link>%s</link><description><![CDATA[%s]]></description><lastBuildDate>%s</lastBuildDate><webMaster>%s</webMaster>%s</channel></rss>';
		
		$elements = '';
		foreach ($this->xml as $row) {
			$author = isset($row['author']) ? '<author>' . $row['author'] . '</author>' : '';
			$title = html_entity_decode_utf8($row['title']);
			$description = html_entity_decode_utf8($row['description']);
			$pubdate = date('D, d M Y H:i:s \G\M\T', $row['pubdate']);

			$elements .= sprintf($each_format, $author, $title, $row['link'], $row['link'], $description, $pubdate);
		}
		
		$title = html_entity_decode_utf8(lang('rss_' . $this->mode));
		$link = 'http://www.rockrepublik.net/';
		$description = html_entity_decode_utf8(lang('rss_desc_' . $this->mode));
		$lastbuild = date('D, d M Y H:i:s \G\M\T', $this->xml[0]['pubdate']);
		$webmaster = 'info@rockrepublik.net';

		header('Content-type: text/xml');
		echo sprintf($full_format, $title, $link, $description, $lastbuild, $webmaster, $elements);

		sql_close();
		exit;
	}
}