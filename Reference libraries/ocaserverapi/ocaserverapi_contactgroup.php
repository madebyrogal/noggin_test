<?php

class OCAServerApi_ContactGroup extends OCAServerApi_Entity {
	public $singular = 'contactgroup';
	public $plural = 'contactgroups';

    public function type() {
        return $this->client->get($this->data['TypeURL']);
    }

	public function change($data) {
        $data['FieldData'] = $this->type()->fieldsToIds($data['FieldData']);
		return $this->_change($data);
	}

	public function delete() {
		return $this->_delete();
	}

	public function createSub($typeUrl, $data) {
        $data['TypeURL'] = $typeUrl;
        $data['FieldData'] = $this->client->get($typeUrl)->fieldsToIds($data['FieldData']);
        return $this->_create($this->url . '/contactgroups', $data);
	}

	public function subs($params=array()) {
        return parent::_Search($this->client, $this->url . '/contactgroups', $params);
	}

	public function contacts($params=array()) {
        return parent::_Search($this->client, $this->url . '/contacts', $params);
	}

	public function allContacts($params=array()) {
            return parent::_Search($this->client, $this->url . '/allcontacts', $params);
	}
    
    /**
     * Create a contact in this group
     * 
     * @param type $typeUrl The type url of the contact (Not the group)
     * @param type $data The data to be saved
     * @return type
     */
	public function createContact($typeUrl, $data) {
        // send the field data (expecting BaseFieldData back)
        $data = $this->client->get($typeUrl)->fieldsToIds($data['FieldData']);
        $data['TypeURL'] = $typeUrl;
        return $this->_create($this->url . '/contacts', $data);
	}

    static public function Search($client, $params=array()) {
        return parent::_Search($client, '/api/v1/contactgroups', $params);
    }
}
