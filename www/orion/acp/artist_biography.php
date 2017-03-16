<?php

if (!defined('IN_APP')) exit;

class __artist_biography extends mac {
    public function __construct() {
        parent::__construct();

        $this->auth('artist');
    }

    public function _home() {
        $this->_artist();

        $this->__home();

        $sql = 'SELECT bio
            FROM _artists
            WHERE ub = ?';
        $bio = sql_field(sql_filter($sql, $this->object->ub), 'bio');

        v_style(array(
            'MESSAGE' => $bio)
        );

        return $this->warning_show();
    }

    private function __home() {
        global $comments;

        if (_button()) {
            $message = request_var('message', '');
            $message = $comments->prepare($message);

            $sql = 'UPDATE _artists SET bio = ?
                WHERE ub = ?';
            sql_query(sql_filter($sql, $message, $this->object->ub));

            $this->warning('ARTIST_BIO_UPDATED');
        }
    }
}
