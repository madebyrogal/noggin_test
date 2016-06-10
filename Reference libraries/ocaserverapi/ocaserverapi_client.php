<?php

class OCAServerApi_Client {
    public $username;
    public $password;
    public $host;
    private $session;
    public $sessionId;
    public $sessionPayload;
    public $entities = array();
    public $logLevel = 0;
    public $uploadSecret = 'SECRET';

    public function __construct($host, $username, $password) {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
        global $db;
        $this->session = new NGCms_Api_Session($db, 'OCAServerApi-'.($this->username));
        if ($this->session->getSessionID()) {
            $this->sessionId = $this->session->getSessionID();
        }
    }

    public function ackTerms($ackCode) {
        $response = $this->call(
            'PUT',
            '/api/v1/session',
            array('TermsAccept' => array('TermsAckCode' => $ackCode))
        );
        if ($response['isOk']) {
            return true;
        } else {
            throw new Exception('Unable to acknowledge terms: ' . $response['statusCode']);
        }
    }

    public function login() {
        $response = $this->call(
            'POST',
            '/api/v1/session',
            array('Username' => $this->username, 'Password' => $this->password),
            false
        );
        if ($response['isOk']) {
            $this->sessionId = $response['payload'][0]['SessionID'];
            if ($this->session) {
                // Log out old session
                if ($s = $this->session->getSessionIDForced()) {
                    // This causes the new session to become invalid
                    /*$this->call(
                        'DELETE',
                        '/api/v1/session',
                        array('X-Session-ID' => $s),
                        false
                    );*/
                }
                // Store new session
                $this->session->setNewSessionID($this->sessionId);
                $this->session->setSessionTTL(0, 0, 0, 60);
                $this->session->save();
            }
            switch ($response['payload'][0]['StateCode']) {
                case 'WAIT_TERMS_AND_CONDITIONS':
                    $this->ackTerms($response['payload'][0]['TermsAckCode']);
                    break;
                case 'AUTHENTICATED':
                    // store the payload returned by OCA
                    $this->sessionPayload = $response['payload'][0];
                    return true;
            }
        } else {
            throw new Exception('Unable to authenticate: ' . $response['statusCode']);
        }
    }
    
    /**
     * Get the session payload as returned by the login
     * @return array
     */
    public function getSessionPayload() {
        return $this->sessionPayload;
    }

    public function curl($type, $url, $headers = array(), $data=null, $auth=true) {

        if ($auth && $this->sessionId === null) {
            $this->login();
        }
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $type);
        curl_setopt($ch, CURLOPT_URL, $this->host . $url);
        if ($this->sessionId !== null) {
            $headers[] = 'X-Session-ID: ' . $this->sessionId;
        }
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HEADER, 1);
        if ($this->logLevel > 0) {
            curl_setopt($ch, CURLINFO_HEADER_OUT, 1);
        }
        if ($type == 'POST') {
            curl_setopt($ch, CURLOPT_POST, 1);
        }
        if (($type == 'POST' || $type == 'PUT') && $data !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        }
        $output = curl_exec($ch);
        if ($this->logLevel > 0) {
            file_put_contents('php://stderr', curl_getinfo($ch, CURLINFO_HEADER_OUT) . "\n\n");
        }
        if (!empty($output)) {
            $parts = explode("\r\n\r\nHTTP/", $output);
            $parts = (count($parts) > 1 ? 'HTTP/' : '').array_pop($parts);
            list($response_headers, $body) = explode("\r\n\r\n", $parts, 2);
            $response = array(
                'statusCode' => curl_getinfo($ch, CURLINFO_HTTP_CODE),
                'isOk' => (curl_getinfo($ch, CURLINFO_HTTP_CODE) >= 200 && curl_getinfo($ch, CURLINFO_HTTP_CODE) <= 299),
                'headers' => array(),
                'body' => $body
            );
            foreach (split("\r?\n", $response_headers) as $response_header) {
                if (preg_match('/^([^\:]+)\: (.*)$/', $response_header, $m)) {
                    $response['headers'][$m[1]] = $m[2];
                }
            }
        }
        if (curl_errno($ch)) {
            throw new Exception('Curl: ' . curl_error($ch));
        }
        return $response;
    }

    /**
     * Calls the OCA server RESTful API and returns a response object.
     *
     * @param string The type of request (i.e. GET/POST/PUT/DELETE)
     * @param string The endpoint URL to call
     * @param array The payload data to send if it is a POST or PUT request
     * @param bool Whether or not to authenticate prior to making this request
     * @returns object The reponse object
     */
    public function call($type, $url, $data=null, $auth=true) {
        $headers = array('Accept: application/json', 'Content-Type: application/json');
        if ($data !== null) $data = json_encode($data);
        $response = $this->curl($type, $url, $headers, $data, $auth);
        $payload = json_decode($response['body'], true);
        if (empty($payload['errors'])) {
            // add pagination data if available
            if (isset($payload['prevPageURL'])) {
                $response['previouspage'] = $payload['prevPageURL'];
            }
            if (isset($payload['nextPageURL'])) {
                $response['nextpage'] = $payload['nextPageURL'];
            }
            $response['payload'] = $payload['responsePayloads'];
        } else {
            if ($payload['errors'][0] == 'Not authorised.') {
                // if the call fails, reset the session
                $this->login();
            }
            throw new OCAServerApi_Exception($payload['errors']);
        }
        return $response;
    }


    public function upload($url, $doc, $auth=true, $method='POST') {
        if (!is_readable($doc['path'])) {
            throw new Exception('Unable to read file: ' . $doc['path']);
        }
        $headers = array('Accept: application/json',
			  'Content-Type: multipart/form-data'
        );
        
        $filename = '/tmp/' . $doc['name'];
        move_uploaded_file($doc['path'], $filename);
        $boundary = '';
        $data = array("filedata" => "@$filename");
        $response = $this->curl($method, $url, $headers, $data, $auth);
        $payload = json_decode($response['body'], true);
        if (empty($payload['errors'])) {
            $response['payload'] = $payload['responsePayloads'];
        } else {
            throw new OCAServerApi_Exception($payload['errors']);
        }
        return $response;
    }

    /**
     * Returns the entity object for the provided URL
     *
     * @param string $url The URL of the entity to return
     * @return OCAServerApi_Entity The entity object
     * @throws Exception if the entity type is unknown
     * @throws Exception if the URL is syntactically invalid
     */
    public function get($url, $entityType=null) {
    	if ($entityType === null && preg_match('/^\/api\/v[0-9]\/([^\/]+)\//', $url, $m)) {
    		$entityType = $m[1];
    	}
    	if ($entityType !== null) {
            if (class_exists($className = 'OCAServerApi_' . $entityType)) {
                return new $className($this, $url);
            } else {
                throw new Exception('Unknown entity: ' . $url);
            }
        } else {
            throw new Exception('Invalid URL: ' . $url);
        }
    }

    public function search($url, $params=array(), $entityType=null) {
    	if ($entityType === null && preg_match('/^\/api\/v[0-9]\/([^\/]+)s($|\/)/', $url, $m)) {
    		$entityType = $m[1];
    	}
    	if ($entityType !== null) {
            if (is_callable($callable = array('OCAServerApi_' . $entityType, 'Search'))) {
                return call_user_func($callable, $this, $params);
            } else {
                throw new Exception('Unknown entity: ' . $url);
            }
        } else {
            throw new Exception('Invalid URL: ' . $url);
        }
    }

    public function requestUpload($field) {

        if (!isset($_FILES)
            || !array_key_exists($field, $_FILES)
            || !is_uploaded_file($_FILES[$field]['tmp_name'])
        ) {
        	throw new Exception('No file uploaded: ' . $field);
        }

        // Check error types
        if ($_FILES[$field]['error'] !== UPLOAD_ERR_OK) {
            switch ($_FILES[$field]['error']) {
                case UPLOAD_ERR_INI_SIZE:
                    // Value: 1; The uploaded file exceeds the upload_max_filesize directive in php.ini.
                    throw new Exception('Uploaded file is larger tha upload_max_filesize directive: ' . $field);
                case UPLOAD_ERR_FORM_SIZE:
                    // Value: 2; The uploaded file exceeds the MAX_FILE_SIZE directive that was specified in the HTML
                    // form.
                    throw new Exception('Uploaded file is larger tha MAX_FILE_SIZE form field: ' . $field);
                case UPLOAD_ERR_PARTIAL:
                    // Value: 3; The uploaded file was only partially uploaded.
                    throw new Exception('Only part of the file was uploaded: ' . $field);
                case UPLOAD_ERR_NO_FILE:
                    // Value: 4; No file was uploaded.
                    throw new Exception('No file was uploaded: ' . $field);
                case UPLOAD_ERR_NO_TMP_DIR:
                    // Value: 6; Missing a temporary folder. Introduced in PHP 4.3.10 and PHP 5.0.3.
                    throw new Exception('No temporary folder to place uploaded file: ' . $field);
                case UPLOAD_ERR_CANT_WRITE:
                    // Value: 7; Failed to write file to disk. Introduced in PHP 5.1.0.
                    throw new Exception('Unable to write uploaded file to disk: ' . $field);
                case UPLOAD_ERR_EXTENSION:
                    // Value: 8; A PHP extension stopped the file upload. PHP does not provide a way to ascertain which
                    // extension caused the file upload to stop; examining the list of loaded extensions with phpinfo()
                    // may help. Introduced in PHP 5.2.0.
                    throw new Exception('An extension stopped the file upload: ' . $field);
                default:
                    throw new Exception('Some unknown error uploading: ' . $field);
            }
        }

        $file = tempnam('/tmp', 'ocaserverapi-');
        copy($_FILES[$field]['tmp_name'], $file);

        return array(
            'upload' => basename($file),
            'hmac' => hash_hmac('sha256', basename($file), $this->uploadSecret),
            'name' => $_FILES[$field]['name'],
            'size' => $_FILES[$field]['size'],
            'mimeType' => $_FILES[$field]['type']
        );
    }
    
//    public function media($encryptedId) {
//        $response = $this->curl('GET', '/media/' . urlencode($encryptedId));
//        ng_dump();
//    }
}
