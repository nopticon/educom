<?php

if (!defined('IN_APP')) exit;

header('Content-type: text/html; charset=utf-8');

class broadcast {
    private $_template;
    private $_title;

    public function __construct() {
        return;
    }

    public function get_title($default = '') {
        return (!empty($this->_title)) ? $this->_title : $default;
    }

    public function get_template($default = '') {
        return (!empty($this->_template)) ? $this->_template : $default;
    }

    private function v($property) {
        if (!isset($this->data->$property)) {
            return false;
        }

        return $this->data->$property;
    }

    public function run() {
        return $this->all();

        // return $this->object();
    }

    private function all() {
        global $user, $config, $comments;

        $sql = 'SELECT *
            FROM _partners
            ORDER BY partner_order';
        $partners = sql_rowset($sql);

        foreach ($partners as $i => $row) {
            if (!$i) _style('partners');

            _style('partners.row', array(
                'NAME' => $row->partner_name,
                'IMAGE' => $row->partner_image,
                'URL' => $config->assets_url . '/style/sites/' . $row->partner_url)
            );
        }

        return;
    }

    private function object() {
        global $user, $config, $comments;

        $this->_title = '';
        $this->_template = '';

        return;
    }
}
