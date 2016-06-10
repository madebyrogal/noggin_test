<?php

class OCAServerApi_AssetType extends OCAServerApi_EntityType {
	public $singular = 'assettype';
	public $plural = 'assettypes';

    /**
     * Creates an asset of this type
     *
     * @param array $data The data to create the asset with
     * @returns string The URL of the newly created asset
     */
	public function create($data) {
        $data['TypeURL'] = $this->url;
        $data['FieldData'] = $this->fieldsToIds($data['FieldData']);
        return $this->_create('/api/v1/assets', $data);
	}

    static public function Search($client, $params) {
        return parent::_Search($client, '/api/v1/assettypes', $params);
    }
}
