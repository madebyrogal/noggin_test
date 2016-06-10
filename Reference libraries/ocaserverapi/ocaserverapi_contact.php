<?php

class OCAServerApi_Contact extends OCAServerApi_Entity {
	public $singular = 'contact';
	public $plural = 'contacts';

    public function type() {
        return $this->client->get($this->data['TypeURL']);
    }

	public function change($data) {
        // contacts do not have the fieldData key in the data array
        $savedata = $this->type()->fieldsToIds($data['FieldData']);
		return $this->_change($savedata);
	}

	public function delete() {
		return $this->_delete();
	}

    static public function Search($client, $params=array()) {
        return parent::_Search($client, '/api/v1/contacts', $params);
    }
}
