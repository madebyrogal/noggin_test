<?php

/**
 * Class OCAServerApi_Shift
 *
 * This class enables CRUD for a shift
 * A shift is always belongs to an object resource allocations (resource)
 */
class OCAServerApi_Shift extends OCAServerApi_Entity {

	public $shiftUrl;

	public function __construct($client, $resourceId) {

		$this->client = $client;
		$this->shiftUrl = "/api/v2/objectresourceallocation/{$resourceId}";
	}

	public function change($id, $data) {
		$this->url = $this->shiftUrl . "/shift/{$id}";
		return $this->_change($data);
	}

	public function delete($id) {
		$this->url = $this->shiftUrl . "/shift/{$id}";
		return $this->_delete();
	}

    public function create($data) {
		return $this->_create($this->shiftUrl . "/shifts", $data);
	}

	public function get($id) {
		$result = $this->_get($this->client, $this->shiftUrl . "/shift/{$id}");
        // needs to get the label of shift type through another OCA call
        if (!empty($result['payload'][0]['TypeURL'])) {
            $shiftType = $this->_get($this->client, $result['payload'][0]['TypeURL']);
            $result['payload'][0]['TypeURL'] = $shiftType['payload'][0]['Label'];
        }

		return $result['payload'][0];
	}

//	public function query($params=array()) {
//		$result = $this->_query($this->client, $this->shiftUrl, $params);
//		return $result['payload'][0]['Blocks'];
//	}

}
