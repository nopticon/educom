<?php

if (!defined('IN_APP')) exit;

class __general_emoticon_update extends mac {
    public function __construct() {
        parent::__construct();

        $this->auth('founder');
    }

    public function _home() {
        global $config, $user, $cache;

        sql_truncate('_smilies');

        $emoticon_path = $config->assets_path . 'emoticon/';
        $process = 0;

        $fp = @opendir($emoticon_path);
        while ($file = @readdir($fp)) {
            if (preg_match('#([a-z0-9]+)\.(gif|png)#is', $file, $part)) {
                $insert = array(
                    'code' => ':' . $part[1] . ':',
                    'smile_url' => $part[0]
                );
                sql_insert('smilies', $insert);

                $process++;
            }
        }
        @closedir($fp);

        $cache->delete('smilies');

        return _pre($process . ' emoticons.');
    }
}
