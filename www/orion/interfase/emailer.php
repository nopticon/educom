<?php

if (!defined('IN_APP')) exit;

class emailer {
    public $msg;
    public $subject;
    public $extra_headers;
    public $addresses;
    public $reply_to;
    public $from;

    public $tpl_msg = array();

    public function __construct() {
        $this->reset();
        $this->reply_to = $this->from = '';
    }

    // Resets all the data (address, template file, etc etc to default
    public function reset() {
        $this->addresses = w();
        $this->vars = $this->msg = $this->extra_headers = '';
    }

    // Sets an email address to send to
    public function email_address($address) {
        $this->addresses['to'] = trim($address);
    }

    public function cc($address) {
        if (strpos($address, '@') === false) {
            global $config;

            $address = $config->sitename . ' <' . $address . '@' . array_key(explode('@', $config->board_email), 1) . '>';
        }

        $this->addresses['cc'][] = trim($address);
    }

    public function bcc($address) {
        if (strpos($address, '@') === false) {
            global $config;

            $address = $config->sitename . ' <' . $address . '@' . array_key(explode('@', $config->board_email), 1) . '>';
        }

        $this->addresses['bcc'][] = trim($address);
    }

    public function replyto($address) {
        if (strpos($address, '@') === false) {
            global $config;

            $address = $address . '@' . array_key(explode('@', $config->board_email), 1);
        }

        $this->reply_to = trim($address);
    }

    public function from($address) {
        if (strpos($address, '@') === false) {
            global $config;

            $address = $config->sitename . ' <' . $address . '@' . array_key(explode('@', $config->board_email), 1) . '>';
        }

        $this->from = trim($address);
    }

    // set up subject for mail
    public function set_subject($subject = '') {
        $this->subject = trim(preg_replace('#[\n\r]+#s', '', $subject));
    }

    // set up extra mail headers
    public function extra_headers($headers) {
        $this->extra_headers .= trim($headers) . nr();
    }

    public function use_template($template_file, $template_lang = '') {
        global $config;

        if (trim($template_file) == '') {
            trigger_error('No template file set');
        }

        if (trim($template_lang) == '') {
            $template_lang = $config->default_lang;
        }

        if (empty($this->tpl_msg[$template_lang . $template_file])) {
            $tpl_file = ROOT.'language/' . $template_lang . '/email/' . $template_file . '.tpl';

            if (!@file_exists(@realpath($tpl_file))) {
                $tpl_file = ROOT.'language/' . $config->default_lang . '/email/' . $template_file . '.tpl';

                if (!@file_exists(@realpath($tpl_file))) {
                    trigger_error('Could not find email template file :: ' . $template_file);
                }
            }

            if (!($fd = @fopen($tpl_file, 'r'))) {
                trigger_error('Failed opening template file :: ' . $tpl_file);
            }

            $this->tpl_msg[$template_lang . $template_file] = fread($fd, filesize($tpl_file));
            fclose($fd);
        }

        $this->msg = $this->tpl_msg[$template_lang . $template_file];

        return true;
    }

    // assign variables
    public function assign_vars($vars) {
        $this->vars = (empty($this->vars)) ? $vars : $this->vars . $vars;
    }

    // Send the mail out to the recipients set previously in var $this->address
    public function send() {
        global $config, $user;

            // Escape all quotes, else the eval will fail.
        $this->msg = str_replace ("'", "\'", $this->msg);
        $this->msg = preg_replace('#\{([a-z0-9\-_]*?)\}#is', "' . $\\1 . '", $this->msg);

        // Set vars
        reset ($this->vars);
        while (list($key, $val) = each($this->vars)) {
            $$key = $val;
        }

        eval("\$this->msg = '$this->msg';");

        // Clear vars
        reset ($this->vars);
        while (list($key, $val) = each($this->vars)) {
            unset($$key);
        }

        // We now try and pull a subject from the email body ... if it exists,
        // do this here because the subject may contain a variable
        $drop_header = '';
        $match = w();
        if (preg_match('#^(Subject:(.*?))$#m', $this->msg, $match)) {
            $this->subject = (trim($match[2]) != '') ? trim($match[2]) : (($this->subject != '') ? $this->subject : 'No Subject');
            $drop_header .= '[\r\n]*?' . preg_quote($match[1], '#');
        } else {
            $this->subject = (($this->subject != '') ? $this->subject : 'No Subject');
        }

        if (preg_match('#^(Charset:(.*?))$#m', $this->msg, $match)) {
            $this->encoding = (trim($match[2]) != '') ? trim($match[2]) : trim($lang['ENCODING']);
            $drop_header .= '[\r\n]*?' . preg_quote($match[1], '#');
        } else {
            $this->encoding = lang('encoding');
        }

        if ($drop_header != '') {
            $this->msg = trim(preg_replace('#' . $drop_header . '#s', '', $this->msg));
        }

        $to = $this->addresses['to'];

        $cc = (isset($this->addresses['cc']) && count($this->addresses['cc'])) ? implode(', ', $this->addresses['cc']) : '';
        $bcc = (isset($this->addresses['bcc']) && count($this->addresses['bcc'])) ? implode(', ', $this->addresses['bcc']) : '';

        // Build header
        $this->extra_headers = (($this->reply_to != '') ? "Reply-to: $this->reply_to\n" : '') . (($this->from != '') ? "From: $this->from\n" : "From: " . $config->board_email . "\n") . "Return-Path: " . $config->board_email . "\nMessage-ID: <" . md5(uniqid(time())) . '@' . substr($config->cookie_domain, 1) . ">\nMIME-Version: 1.0\nContent-type: text/plain; charset=" . $this->encoding . "\nContent-transfer-encoding: 8bit\nDate: " . date('r', time()) . "\nX-Priority: 3\nX-MSMail-Priority: Normal\n" . $this->extra_headers . (($cc != '') ? "Cc: $cc\n" : '')  . (($bcc != '') ? "Bcc: $bcc\n" : '');

        // Send message ... removed $this->encode() from subject for time being
        $empty_to_header = ($to == '') ? true : false;
        $to = ($to == '') ? (($config->sendmail_fix) ? ' ' : 'Undisclosed-recipients:;') : $to;

        $this->subject = entity_decode($this->subject);
        $this->msg = entity_decode($this->msg);

        $result = @mail($to, $this->subject, preg_replace("#(?<!\r)\n#s", "\n", $this->msg), $this->extra_headers, "-f{$config->board_email}");

        if (!$result && !$config->sendmail_fix && $empty_to_header) {
            $to = ' ';

            set_config('sendmail_fix', 1);

            $result = @mail($to, $this->subject, preg_replace("#(?<!\r)\n#s", "\n", $this->msg), $this->extra_headers, "-f{$config->board_email}");
        }

        if (!$result) {
            return false;
        }

        return true;
    }

    // Encodes the given string for proper display for this encoding ... nabbed
    // from php.net and modified. There is an alternative encoding method which
    // may produce lesd output but it's questionable as to its worth in this
    // scenario IMO
    public function encode($str) {
        if ($this->encoding == '') {
            return $str;
        }

        // define start delimimter, end delimiter and spacer
        $end = "?=";
        $start = "=?$this->encoding?B?";
        $spacer = "$end\r\n $start";

        // determine length of encoded text within chunks and ensure length is even
        $length = 75 - strlen($start) - strlen($end);
        $length = floor($length / 2) * 2;

        // encode the string and split it into chunks with spacers after each chunk
        $str = chunk_split(base64_encode($str), $length, $spacer);

        // remove trailing spacer and add start and end delimiters
        $str = preg_replace('#' . preg_quote($spacer, '#') . '$#', '', $str);

        return $start . $str . $end;
    }

    //
    // Attach files via MIME.
    //
    public function attachFile($filename, $mimetype = "application/octet-stream", $szFromAddress, $szFilenameToDisplay) {
        global $lang;
        $mime_boundary = "--==================_846811060==_";

        $this->msg = '--' . $mime_boundary . "\nContent-Type: text/plain;\n\tcharset=\"" . $lang['ENCODING'] . "\"\n\n" . $this->msg;

        if ($mime_filename) {
            $filename = $mime_filename;
            $encoded = $this->encode_file($filename);
        }

        $fd = fopen($filename, "r");
        $contents = fread($fd, filesize($filename));

        $this->mimeOut = "--" . $mime_boundary . "\n";
        $this->mimeOut .= "Content-Type: " . $mimetype . ";\n\tname=\"$szFilenameToDisplay\"\n";
        $this->mimeOut .= "Content-Transfer-Encoding: quoted-printable\n";
        $this->mimeOut .= "Content-Disposition: attachment;\n\tfilename=\"$szFilenameToDisplay\"\n\n";

        if ($mimetype == 'message/rfc822') {
            $this->mimeOut .= "From: ".$szFromAddress."\n";
            $this->mimeOut .= "To: ".$this->emailAddress."\n";
            $this->mimeOut .= "Date: ".date("D, d M Y H:i:s") . " UT\n";
            $this->mimeOut .= "Reply-To:".$szFromAddress."\n";
            $this->mimeOut .= "Subject: ".$this->mailSubject."\n";
            $this->mimeOut .= "MIME-Version: 1.0\n";
        }

        $this->mimeOut .= $contents."\n";
        $this->mimeOut .= "--" . $mime_boundary . "--" . "\n";

        return $out;
        // added -- to notify email client attachment is done
    }

    public function getMimeHeaders($filename, $mime_filename = '') {
        $mime_boundary = "--==================_846811060==_";

        if ($mime_filename) {
            $filename = $mime_filename;
        }

        $out = "MIME-Version: 1.0\n";
        $out .= "Content-Type: multipart/mixed;\n\tboundary=\"$mime_boundary\"\n\n";
        $out .= "This message is in MIME format. Since your mail reader does not understand\n";
        $out .= "this format, some or all of this message may not be legible.";

        return $out;
    }

    //
   // Split string by RFC 2045 semantics (76 chars per line, end with \r\n).
    //
    public function myChunkSplit($str) {
        $stmp = $str;
        $len = strlen($stmp);
        $out = "";

        while ($len > 0) {
            if ($len >= 76) {
                $out .= substr($stmp, 0, 76) . "\r\n";
                $stmp = substr($stmp, 76);
                $len = $len - 76;
            } else {
                $out .= $stmp . "\r\n";
                $stmp = "";
                $len = 0;
            }
        }
        return $out;
    }

    //
   // Split the specified file up into a string and return it
    //
    public function encode_file($sourcefile) {
        if (is_readable(@realpath($sourcefile))) {
            $fd = fopen($sourcefile, 'r');
            $contents = fread($fd, filesize($sourcefile));
            $encoded = $this->myChunkSplit(base64_encode($contents));
            fclose($fd);
        }

        return $encoded;
    }
}
