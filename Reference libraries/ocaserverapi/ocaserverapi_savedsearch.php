<?php

class OCAServerApi_SavedSearch {
    
    public $client;
    public $savedSearchId;

    public function __construct($client, $savedSearchId) {
        $this->client = $client;
        $this->savedSearchId = $savedSearchId;
    }
    
    public function execute($params=array()) {
        $response = $this->client->call('GET', '/api/v1/savedsearch/' . urlencode($this->savedSearchId) . '?' . http_build_query($params));
        return $response['payload'];
    }
    
    public function describe() {
        $response = $this->client->call('GET', '/api/v1/savedsearch/' . urlencode($this->savedSearchId) . '/describe');
        return $response['payload'];
    }

    static public function Search($client, $params) {
        return parent::_Search($client, '/api/v1/savedsearches', $params);
    }

}
