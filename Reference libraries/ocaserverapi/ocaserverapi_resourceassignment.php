<?php

/***
 * Class OCAServerApi_ResourceAssignment
 *
 * A Resource Assignment is an entity that contains a tree Resource Stucture Nodes
 * A Resource Assignment can be stand-alone, or assigned to another entity (Asset ot Event)
 */
class OCAServerApi_ResourceAssignment extends OCAServerApi_Entity {
	public $singular = 'resourceassignment';
	public $plural = 'resourceassignments';
    protected $type;

    public function type() {
        if (!isset($this->type)) {
            $this->type = $this->client->get($this->data['TypeURL']);
        }
        return $this->type;
    }

    public function create($typeUrl, $data) {
        $data['TypeURL'] = $typeUrl;
        $data['FieldData'] = $this->client->get($typeUrl)->fieldsToIds($data['FieldData']);
        return $this->_create('/api/v2/resourceassignments', $data);
    }

	public function change($data) {
        $data['FieldData'] = $this->type()->fieldsToIds($data['FieldData']);
		return $this->_change($data);
	}

	public function delete() {
		return $this->_delete();
	}

    static public function Search($client, $params=array()) {
        return parent::_Search($client, '/api/v2/resourceassignments', $params);
    }

    public function get($url) {
        return $this->_get($this->client, $url);
    }

    public function query($params=array()) {
        $result = $this->_query($this->client, $this->url . $this->plural, $params);
        return $result['payload'];
    }

    public function getResourceStructureNodes() {
        $result = $this->_get($this->client, $this->url . '/resourcestructurenodes');
        return $result['payload'][0]['Nodes'];
    }

    public function confirmObjectResourceAllocations($data = array('Type' => 'manual', 'Status' => 'confirmed', 'All' => 'any')) {
        return $this->client->call('PUT', $this->url . '/confirm', $data);
    }
}
