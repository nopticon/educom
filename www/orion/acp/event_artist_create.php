<?php

if (!defined('IN_APP')) exit;

class __event_artist_create extends mac {
	public function __construct() {
		parent::__construct();

		$this->auth('mod');
	}

	/*
	Show form listing all event available to this artist.
	*/
	public function _home() {
		global $config, $user, $cache;

		$this->_artist();

		if ($this->create()) {
			return;
		}

		$ini_year = mktime(0, 0, 0, 1, 1);

		$sql = 'SELECT *
			FROM _events
			WHERE date > ??
			ORDER BY date DESC';
		$events = sql_rowset(sql_filter($sql, $ini_year));

		$last_month = '';
		foreach ($events as $i => $row) {
			if (!$i) _style('events');

			$row_month = ucfirst($user->format_date($row->date, 'F \'y'));

			if ($last_month != $row_month) {
				$last_month = $row_month;

				_style('events.month', array(
					'NAME' => $row_month)
				);
			}

			_style('events.month.row', array(
				'ID' => $row->id,
				'TITLE' => $row->title,
				'DATE' => $user->format_date($row->date))
			);
		}

		return;
	}

	/*
	Assign an event to selected artist.
	*/
	private function create() {
		$v = _request(array('event' => 0));

		if (_empty($v)) {
			return;
		}

		$sql = 'SELECT id, event_alias
			FROM _events
			WHERE id = ?';
		if (!$event = sql_fieldrow(sql_filter($sql, $v->event))) {
			return;
		}

		$sql = 'SELECT ub
			FROM _artists_events
			WHERE a_artist = ?
				AND a_event = ?';
		if (sql_field(sql_filter($sql, $this->object->ub, $v->event))) {
			return;
		}

		$sql_insert = array(
			'a_artist' => $this->object->ub,
			'a_event' => $event->id
		);
		sql_insert('artists_events', $sql_insert);

		return redirect(s_link('events', $event->event_alias));
	}
}
