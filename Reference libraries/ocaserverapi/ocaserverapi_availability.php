<?php

class OCAServerApi_Availability extends OCAServerApi_Entity {

	public $availabilityUrl;

	public function __construct($client, $type, $objectId) {
		$this->client = $client;
		$this->availabilityUrl = "/api/v2/availabilities/{$type}/{$objectId}";
	}

	public function change($id, $data) {
		$this->url = $this->availabilityUrl . "/{$id}";
		return $this->_change($data);
	}

	public function delete($id) {
		$this->url = $this->availabilityUrl . "/{$id}";
		return $this->_delete();
	}

    public function create($data) {
		return $this->_create($this->availabilityUrl, $data);
	}

	public function get($id) {
		$result = $this->_get($this->client, $this->availabilityUrl . "/{$id}");
		return $result['payload'][0];
	}

	public function query($params=array()) {
		$result = $this->_query($this->client, $this->availabilityUrl, $params);
		return $result['payload'][0]['Blocks'];
	}

}
