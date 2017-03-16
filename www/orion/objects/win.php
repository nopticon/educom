<?php

if (!defined('IN_APP')) exit;

class win {
    private $object;
    private $_title;
    private $_template;

    public function __construct() {
        return;
    }

    public function get_title($default = '') {
        return (!empty($this->_title)) ? $this->_title : $default;
    }

    public function get_template($default = '') {
        return (!empty($this->_template)) ? $this->_template : $default;
    }

    public function run() {
        $alias = request_var('alias', '');

        if (empty($alias)) {
            return $this->elements();
        }

        $sql = 'SELECT *
            FROM _win
            WHERE win_alias = ?';
        if (!$this->object = sql_fieldrow(sql_filter($sql, $alias))) {
            fatal_error();
        }

        return $this->run_object();
    }

    private function elements() {
        $sql = 'SELECT *
            FROM _win
            ORDER BY win_date';
        $win = sql_rowset($sql);

        foreach ($win as $i => $row) {
            if (!$ui) _style('win');

            _style('win.row', array(

            ));
        }
        return;
    }

    private function run_object() {
        if (_button()) {
            return $this->store();
        }

        return;
    }

    private function store() {
        return;
    }
}
