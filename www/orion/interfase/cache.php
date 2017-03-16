<?php

if (!defined('IN_APP')) exit;

class cache {
    public $cache = array();
    public $use = true;
    private $extension = '.json';

    public function __construct() {
        if (!defined('USE_CACHE')) {
            $this->use = false;
        }

        return $this->use;
    }

    public function config() {
        $sql = 'SELECT *
            FROM _application';
        $config = sql_rowset($sql, 'config_name', 'config_value');

        return $config;
    }

    private function path($str) {
        global $config;

        return $config->cache_path . $str . $this->extension;
    }

    public function get($var) {
        if (!$this->use) {
            return false;
        }

        global $config;

        $filename = $this->path($var);

        if (@file_exists($filename)) {
            ob_start();
            require_once($filename);

            $content = ob_get_contents();
            ob_end_clean();

            if (!empty($content)) {
                return json_decode($content);
            }

            return true;
        }

        $this->delete($var);

        return;
    }

    public function save($var, $data) {
        global $config;

        if (!$this->use) {
            return;
        }

        $filename = $this->path($var);

        if ($fp = @fopen($filename, 'w')) {
            $file_buffer = json_encode($data);

            @flock($fp, LOCK_EX);
            @fwrite($fp, $file_buffer);
            @flock($fp, LOCK_UN);
            @fclose($fp);

            _chmod($filename, $config->mask);
        }

        return $data;
    }

    public function delete($list) {
        global $config;

        if (!$this->use) {
            return;
        }

        foreach (w($list) as $var) {
            _rm($this->path($var));
        }

        return;
    }
}
