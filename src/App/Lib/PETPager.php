<?php

namespace App\Lib;

use App\App;
use Psr\Log\LoggerInterface;
/**
 * $Id: pager.lib 6796 2008-11-25 23:13:27Z jesper $
 * 
 * Alphanumeric Paging Library
 *  - supports SMTP, PET over TCP
 *
 * ------ Terminology ----------
 *  TAP => Tele-locator Alphanumeric Protocol
 *  PET => Pager Entry Terminal
 *  LPH => Link Paging Host
 *  PUD => PET User Device
 *  PG1 => Standard PET Mode
 *  PG3 => Allows Extra 'Destination' Code (Not implemented)
 * @author jesper
 * @author-mod Tomek Rogalski <tomek@bitnoi.se>
 */
// These are only relevent to PETPager::
define('PET_ESC', chr(0x1B));
define('PET_STX', chr(0x02));
define('PET_ETX', chr(0x03));
define('PET_EOT', chr(0x04));
define('PET_ACK', chr(0x06));
define('PET_CR', chr(0x0D));
define('PET_NAK', chr(0x15));
define('PET_RS', chr(0x1E));

define('PET_ERR_BADPAGERNUM', '01');
define('PET_ERR_BADDESTINATION', '02');
define('PET_ERR_MSGINVALID', '03');
define('PET_ERR_MSGTOOLONG', '04');
define('PET_ERR_STXMISSING', '05');
define('PET_ERR_ETXMISSING', '06');
define('PET_ERR_CHECKSUMFMT', '07');
define('PET_ERR_TIMEOUT', '08');
define('PET_ERR_OUTOFSERVICE', '09');
define('PET_ERR_CRTIMEOUT', '10');


define('PET_MODE_PG1', 'PG1');
define('PET_MODE_PG3', 'PG3');

define('PET_LOGIN_RETRIES', 3);
define('PET_LOGIN_PACING', 2); // In seconds
define('PET_SEND_RETRIES', 3);
define('PET_SEND_MAXLEN', 239); // Max Msg Characters

/**
 * PETPager objects are used to send Alpha-numeric pager
 * messages via PET (Pager Entry Terminal) gateways over
 * TCP/IP, the object must be instantiated to specify the
 * target host/port address and ->login() must be called
 * to connect & authenticate to the PET gateway before
 * using the ->send() method
 */
class PETPager implements PETPagerInterface
{

    public $loginid;
    public $mode;
    public $socket;
    public $host;
    public $port;
    public $_buffer;
    public $errMsg; //will hold the most recent error message
    public $_iso_8859_1;
    
    /**
     * Monolog Logger
     * @var Logger $logger
     */
    public $logger;
    
    /**
     * Constructor
     * @param LoggerInterface $logger
     */
    public function __construct(LoggerInterface $logger)
    {
        // This is needed since the Paging Host could use anything and
        // without it we timeout waiting for a unix line ending
        assert(ini_get('auto_detect_line_endings'));
        $this->logger       = $logger;
        $this->socket       = false;
        $this->_buffer      = array();
        $this->errMsg       = ""; //initially no errors
        $this->_iso_8859_1  = array(
            chr(0xB5) => 'u',
            chr(0xC0) => 'A',
            chr(0xC1) => 'A',
            chr(0xC2) => 'A',
            chr(0xC3) => 'A',
            chr(0xC4) => 'A',
            chr(0xC5) => 'A',
            chr(0xC7) => 'C',
            chr(0xC8) => 'E',
            chr(0xC9) => 'E',
            chr(0xCA) => 'E',
            chr(0xCB) => 'E',
            chr(0xCC) => 'I',
            chr(0xCD) => 'I',
            chr(0xCE) => 'I',
            chr(0xCF) => 'I',
            chr(0xD0) => 'D',
            chr(0xD1) => 'N',
            chr(0xD2) => 'O',
            chr(0xD3) => 'O',
            chr(0xD4) => 'O',
            chr(0xD5) => 'O',
            chr(0xD6) => 'O',
            chr(0xD7) => 'x',
            chr(0xD8) => 'O',
            chr(0xD9) => 'U',
            chr(0xDA) => 'U',
            chr(0xDB) => 'U',
            chr(0xDC) => 'U',
            chr(0xDD) => 'Y',
            chr(0xDF) => 'B',
            chr(0xE0) => 'a',
            chr(0xE1) => 'a',
            chr(0xE2) => 'a',
            chr(0xE3) => 'a',
            chr(0xE4) => 'a',
            chr(0xE5) => 'a',
            chr(0xE7) => 'c',
            chr(0xE8) => 'e',
            chr(0xE9) => 'e',
            chr(0xEA) => 'e',
            chr(0xEB) => 'e',
            chr(0xEC) => 'i',
            chr(0xED) => 'i',
            chr(0xEE) => 'i',
            chr(0xEF) => 'i',
            chr(0xF1) => 'n',
            chr(0xF2) => 'o',
            chr(0xF3) => 'o',
            chr(0xF4) => 'o',
            chr(0xF5) => 'o',
            chr(0xF6) => 'o',
            chr(0xF8) => 'o',
            chr(0xF9) => 'u',
            chr(0xFA) => 'u',
            chr(0xFA) => 'u',
            chr(0xFB) => 'u',
            chr(0xFC) => 'u',
            chr(0xFD) => 'y',
            chr(0xFF) => 'y'
        );
    }

    /**
     * Initialization
     * @param ip $host
     * @param integer $port
     */
    public function init($host, $port)
    {
        $this->host = $host;
        $this->port = $port;
    }

    // Asumes /r or /r/n termination only
    public function _readline($fp)
    {
        $line = '';

        while (($c = fgetc($fp)) !== false) {
            // Leading \n is just hang over from the last line
            if (strlen($line) == 0 && $c == "\n")
                continue;
            $line .= $c;
            if ($c == "\r")
                return $line;
        }
        return $line;
    }

    /**
     * Get line from server
     * @return string
     */
    public function getline()
    {
        if (empty($this->_buffer)) {
            $tmp = $this->_readline($this->socket);
            if (strlen($tmp) > 0) {
                $tmp = str_replace("\r\n", "\n", $tmp);
                $tmp = str_replace("\r", "\n", $tmp);
                $tmp = rtrim($tmp, "\n");
                $this->_buffer = explode("\n", $tmp);
            }
        }

        $e = array_shift($this->_buffer);
        //print date("r") . " ";
        //print "HUT: " . od($e);
        //ob_flush();
        return $e;
    }

    /**
     * Put line to server
     * @param string $line
     */
    public function putline($line)
    {
        //print date("r") . " ";
        //print "NOG: " . od($line);
        //ob_flush();
        fwrite($this->socket, $line);
        fflush($this->socket);
    }

    /**
     * toLog() just a stub for logging to error_log and updating $this->errMsg with a 'friendly' message
     * @param string $method the method an error occurred in
     * @param string $line the line to log
     * @param string $friendly something friendly to put in the UI
     */
    public function toLog($method = "", $line = "", $friendly = "")
    {
        $this->logger->addInfo("PETPager::{$method}(): {$line}" . " " . $friendly);
        $this->errMsg = $friendly;
        
        return true;
    }

    /**
     * Login to server
     * @param integer $loginid
     * @param string $mode
     * @return boolean
     */
    public function login($loginid, $mode = PET_MODE_PG1)
    {
        assert($mode == PET_MODE_PG1); // PG3 is not supported -> || $mode == PET_MODE_PG3);

        if (!$this->socket) {
            $this->socket = fsockopen($this->host, $this->port, $errno, $errstr);
        }

        // Failed to connect
        if (!$this->socket) {
            $this->toLog("login", "service={$this->host}:{$this->port} fsockopen() failed, errno={$errno}, errstr='{$errstr}'", "Unable to connect to service");
            
            return false;
        }

        $this->mode = $mode;
        $this->loginid = $loginid;

        // Retry PET_LOGIN_RETRIES times, PET_LOGIN_PACING second pacing
        $retry = 0;
        $resp = "";
        while ($resp != "ID=" && $retry < PET_LOGIN_RETRIES) {
            if ($retry > 0) {
                $this->toLog("login", "service={$this->host}:{$this->port} timeout waiting for 'ID=', retry={$retry}, resp='{$resp}'", "A timeout occurred on service login");
                sleep(PET_LOGIN_PACING);
            }
            $this->putline(PET_CR);
            $resp = $this->getline();
            $retry++;
        }

        if ($resp != "ID=") {
            $this->toLog("login", "service={$this->host}:{$this->port} timeout waiting for 'ID=', resp='{$resp}'", "A timeout occurred waiting for service response");
            $this->logout();
            
            return false;
        }

        $this->putline(PET_ESC . $this->mode . $this->loginid . PET_CR);
        $resp = $this->getline();
        if ($resp[0] != PET_ESC && $resp[0] != PET_NAK && $resp[0] != PET_ACK) {
            // Got a text message, get the code from the next line
            $msg = $resp;
            $resp = $this->getline();
        }

        // If ! <ACK> then an error occured
        if ($resp != PET_ACK) {
            $this->toLog("login", "service={$this->host}:{$this->port} logn failed, expected ACK, resp='{$resp}', msg='{$msg}'", "Service failed to acknowledge");
            $this->logout();
            
            return false;
        }

        $resp = $this->getline();
        if ($resp[0] != PET_ESC) {
            $msg = $resp;
            $resp = $this->getline();
        }

        // <ESC>[p<CR> indicates the server is ready for messages
        // Otherwise assume an error has occured & disconnect
        if ($resp != (PET_ESC . "[p")) {
            $this->toLog("login", "service={$this->host}:{$this->port} failed, expected message go-ahead '<ESC>[p', got resp='{$resp}'", "Service not ready to accept messages");
            $this->logout();
            
            return false;
        }
        $this->toLog("login", "service={$this->host}:{$this->port} OK");
        
        return true;
    }

    /**
     * Logout from the PET gateway and close the
     * TCP socket
     */
    public function logout()
    {
        $this->toLog("logout", "service={$this->host}:{$this->port}");
        assert($this->socket !== false);
        $this->putline(PET_EOT . PET_CR);
        $resp = $this->getline();
        if ($resp[0] != PET_ESC) {
            $msg = $resp;
            $resp = $this->getline();
        }
        fclose($this->socket);
        $this->socket = false;
        
        return $resp;
    }

    /**
     * Send message to server
     * @param integer $pagerid
     * @param string $message
     * @param integer $retry
     * @return boolean
     */
    public function send($pagerid, $message, $retry = 0)
    {
        if ($this->socket == false){
         
            return false;
        }

        if ($retry >= PET_SEND_RETRIES) {
            $this->toLog("send", "failed pager='{$pagerid}', retry count exceeded ({$retry})", "Send failed, too many retries");
            
            return false;
        }

        if (!preg_match('/^[0-9 ]+$/', $pagerid)){
        
            return false;
        }

        $this->toLog("send", "pager='{$pagerid}', retry={$retry}", "");

        // \r and \n are illegal, multiple spaces are compressed
        $message = preg_replace("(\r|\n)", " ", $message);
        $message = preg_replace("/\ \ +/", " ", $message);

        // Translate ISO-8859-1 entities
        $message = $this->translate_iso8859_1($message);
        if (!$this->is_7bit_clean($message)) {
            // Remove 8bit characters
            $message = $this->clean_8bit($message);
            $this->toLog("send", "warning pager='{$pagerid}', message not 7 bit clean, stripped 8-bit chars, original msg='{$message}'", "");
        }

        if (strlen($message) > PET_SEND_MAXLEN) {
            $message = substr($message, 0, PET_SEND_MAXLEN);
        }

        // Build a request & send it
        $req = PET_STX . $pagerid . PET_CR . $message . PET_CR . PET_ETX;
        $tosend = $req . $this->checksum($req) . PET_CR;
        $this->putline($tosend);

        $resp = $this->getline();
        if (!in_array($resp[0], array(PET_ACK, PET_NAK, PET_RS, PET_ESC))) {
            $msg = $resp;
            $resp = $this->getline();
        }

        if ($resp[0] == PET_ACK) {
            $this->toLog("send", "response=ACK pager='{$pagerid}', retry='{$retry}'", "");
            
            return true;
        } else if ($resp[0] == PET_NAK) {
            $this->toLog("send", "response=NAK (bad checksum?) pager='{$pagerid}', retry='{$retry}'", "Send failed, service not acknowledging");
            
            // Bad checksum (corrupted?) try again
            return $this->send($pagerid, $message, ++$retry);
        } else if ($resp[0] == PET_ESC && $resp[1] == PET_EOT) {
            // We have been forced to logout, login and try again
            $this->toLog("send", "response=EOT (forced logout) pager='{$pagerid}', retry='{$retry}'", "Send failed, service forced log out");
            $this->logout();
            if ($this->login($this->loginid, $this->mode)) {
                
                return $this->send($pagerid, $message, ++$retry);
            } else {
                $this->toLog("send", "logged out at host request (EOT), couldn't log back in. pager='{$pagerid}', retry='{$retry}'", "Send failed, could not log in");
                
                // We couldn't log back in for some reason
                return false;
            }
        } else if ($resp[0] == PET_RS) {
            // Error, the reason code is in $msg (01-10)
            if ($msg == PET_ERR_MSGINVALID) {
                // The message was modified and sent, log a warning but return true
                $this->toLog("send", "PET_ERR_MSGINVALID msg='{$message}', pager='{$pagerid}' retry='{$retry}'", "");
                
                return true;
            } else if ($msg == PET_ERR_MSGTOOLONG) {
                // The message was modified and sent, log a warning but return true
                $this->toLog("send", "PET_ERR_MSGTOOLONG msg='{$message}', pager='{$pagerid}' retry='{$retry}'", "");
                
                return true;
            } else {
                $this->toLog("send", "ERROR='{$msg}' msg='{$message}', pager='{$pagerid}' retry='{$retry}'", "Send failed");
            }
            
            return false;
        } else {
            $this->toLog("send", "Unknown error condition response='{$msg},{$resp}' msg='{$message}', pager='{$pagerid}' retry='{$retry}'", "Send failed, unknown error");
            
            // Error, unknown condition
            return false;
        }
    }

    /**
     * Calculate the PET checksum of $message
     * returns checksum of $message
     */
    public function checksum($message)
    {

        // Meesage must start with STX and end with ETX
        //assert(substr($message, 0, 1) == PET_STX);
        //assert(substr($msssage, -1)   == PET_ETX);
        // Sum all the ASCII values in $message
        $sum = 0;
        for ($i = 0; $i < strlen($message); $i++) {
            $sum += ord($message[$i]);
        }

        // Use the least significant 3 nibbles only
        $sum &= 0x0FFF;

        $result = chr(((($sum & 0x0F00) >> 8) + ord('0')))
                . chr(((($sum & 0x00F0) >> 4) + ord('0')))
                . chr(((($sum & 0x000F) >> 0) + ord('0')));

        return $result;
    }

    // Translate an 8bit ISO-8559-1 string into the nearest equivalent
    // 7-bit string, some 8-bit characters may remain
    public function translate_iso8859_1($str)
    {

        return strtr($str, $this->_iso_8859_1);
    }

    public function is_7bit_clean($str)
    {
        for ($i = 0; $i < strlen($str); $i++) {
            if (ord($str{$i}) > 127) {
                return false;
            }
        }
        return true;
    }

    public function clean_8bit($str)
    {
        $cleanstr = '';
        for ($i = 0; $i < strlen($str); $i++) {
            if (ord($str{$i}) <= 127) {
                $cleanstr .= $str{$i};
            }
        }
        return $cleanstr;
    }

}
