<?php

class OCAServerApi_EmeType extends OCAServerApi_EntityType {
	public $singular = 'emetype';
	public $plural = 'emetypes';

    /**
     * Creates an EME of this type
     *
     * @param array $data The data to create the EME with
     * @returns string The URL of the newly created EME
     */
	public function create($data) {
        $data['TypeURL'] = $this->url;
        $data['FieldData'] = $this->fieldsToIds($data['FieldData']);
        return $this->_create('/api/v1/eme/0/emes', $data);
	}

    static public function Search($client, $params) {
        return parent::_Search($client, '/api/v1/emetypes', $params);
    }
}
