<?php

namespace App\Lib;

/*
 * Interface to PETPager
 */
interface PETPagerInterface
{

    /**
     * Initialization
     * @param ip $host
     * @param integer $port
     */
    public function init($host, $port);

    // Asumes /r or /r/n termination only
    public function _readline($fp);

    /**
     * Get line from server
     */
    public function getline();

    /**
     * Put line to server
     * @param string $line
     */
    public function putline($line);

    /**
     * toLog() just a stub for logging to error_log and updating $this->errMsg with a 'friendly' message
     * @param string $method the method an error occurred in
     * @param string $line the line to log
     * @param string $friendly something friendly to put in the UI
     */
    public function toLog($method = "", $line = "", $friendly = "");

    /**
     * Login to server
     * @param integer $loginid
     * @param string $mode
     */
    public function login($loginid, $mode = PET_MODE_PG1);

    /**
     * Logout from the PET gateway and close the
     * TCP socket
     */
    public function logout();

    /**
     * Send message to server
     * @param integer $pagerid
     * @param string $message
     * @param integer $retry
     */
    public function send($pagerid, $message, $retry = 0);

    /**
     * Calculate the PET checksum of $message
     * returns checksum of $message
     */
    public function checksum($message);

    // Translate an 8bit ISO-8559-1 string into the nearest equivalent
    // 7-bit string, some 8-bit characters may remain
    public function translate_iso8859_1($str);

    public function is_7bit_clean($str);

    public function clean_8bit($str);
}
