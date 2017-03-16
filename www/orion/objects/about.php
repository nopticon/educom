<?php

if (!defined('IN_APP')) exit;

class about {
    public $data = array();
    public $timetoday = 0;

    private $_template;
    private $_title;

    public function __construct() {
        global $user;

        $current_time = time();
        $minutes = date('is', $current_time);
        $this->timetoday = (int) ($current_time - (60 * intval($minutes[0].$minutes[1])) - intval($minutes[2].$minutes[3])) - (3600 * $user->format_date($current_time, 'H'));

        return;
    }

    public function get_title($default = '') {
        return (!empty($this->_title)) ? $this->_title : $default;
    }

    public function get_template($default = '') {
        return (!empty($this->_template)) ? $this->_template : $default;
    }

    public function v($property) {
        if (!isset($this->data->$property)) {
            return false;
        }

        return $this->data->$property;
    }

    public function run() {
        /*$event_alias = request_var('alias', '');

        if (empty($event_alias)) {
            return $this->all();
        }

        if (!preg_match('#[a-z0-9\_\-]+#i', $event_alias)) {
            fatal_error();
        }

        $event_field = (!is_numb($event_alias)) ? 'event_alias' : 'id';

        $sql = 'SELECT *
            FROM _events
            WHERE ?? = ?';
        if (!$this->data = sql_fieldrow(sql_filter($sql, $event_field, $event_alias))) {
            fatal_error();
        }*/

        return $this->object();
    }

    public function object() {
        global $auth, $user, $config, $comments;

        $mode = request_var('mode', '');



        $this->_title = $this->v('title');
        $this->_template = 'events.view';

        return true;
    }
}
