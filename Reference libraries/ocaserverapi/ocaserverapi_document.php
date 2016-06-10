<?php

class OCAServerApi_Document {
	public $client;
	public $url;
	public $body = null;
    public $headers = array();
    public $sendHeaders = array('Expires', 'Cache-Control', 'Pragma', 'Content-Disposition', 'Content-Type');

	public function __construct($client, $url) {
		$this->client = $client;
        $this->url = $url;
        $rawData = $this->client->call('GET', $this->url);
        if ($rawData['statusCode'] == 200) {
            $this->headers = $rawData['headers'];
            $this->body = $rawData['body'];
        }
	}

    public function download() {
		HTTP::setStatus(200);
        foreach ($this->headers as $key => $val) {
            if (array_search($key, $this->sendHeaders) !== false) {
                header($key . ': '  . $val);
            }
        }
        print $this->body;
        exit;
    }
}
