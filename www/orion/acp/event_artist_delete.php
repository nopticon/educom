<?php

if (!defined('IN_APP')) exit;

class __event_artist_delete extends mac {
	public function __construct() {
		parent::__construct();

		$this->auth('artist');
	}

	/*
	Show all events associated to this artist.
	*/
	public function _home() {
		global $config, $user, $cache;

		$this->_artist();

		if ($this->remove()) {
			return;
		}

		$sql = 'SELECT *
			FROM _events e, _artists_events a
			WHERE a.a_artist = ?
				AND a.a_event = e.id
			ORDER BY e.date DESC';
		$result = sql_rowset(sql_filter($sql, $this->object->ub));

		foreach ($result as $i => $row) {
			if (!$i) _style('events');

			_style('events.row', array(
				'ID' => $row->id,
				'TITLE' => $row->title,
				'DATE' => $user->format_date($row->date))
			);
		}

		return;
	}

	/*
	Remove selected events from this artist.
	*/
	private function remove() {
		$v = _request(array('event' => 0));

		if (_empty($v)) {
			return;
		}

		$sql = 'SELECT *
			FROM _events
			WHERE id = ?';
		if (!$row = sql_fieldrow(sql_filter($sql, $event))) {
			_pre('El evento no existe.', true);
		}

		$e_artist = explode(nr(), $artist);
		foreach ($e_artist as $row) {
			$subdomain = get_subdomain($row);

			$sql = 'SELECT *
				FROM _artists
				WHERE subdomain = ?';
			if ($a_row = sql_fieldrow(sql_filter($sql, $subdomain))) {
				$sql = 'DELETE FROM _artists_events
					WHERE a_artist = ?
						AND a_event = ?';
				sql_query(sql_filter($sql, $a_row->ub, $event));
			}
		}

		return redirect(s_link('events', $row->event_alias));
	}
}
