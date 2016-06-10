<?php

class OCAServerApi_ResourceAssignmentType extends OCAServerApi_EntityType {
	public $singular = 'resourceassignmenttype';
	public $plural = 'resourceassignmenttypes';

    /**
     * Creates an asset of this type
     *
     * @param array $data The data to create the asset with
     * @returns string The URL of the newly created asset
     */
	public function create($data) {
        $data['TypeURL'] = $this->url;
        $data['FieldData'] = $this->fieldsToIds($data['FieldData']);
        return $this->_create('/api/v1/resourceassignmenttypes', $data);
	}

    static public function GetAll($client) {
        return parent::_Search($client, '/api/v1/resourceassignmenttypes', $params=array());
    }
}
