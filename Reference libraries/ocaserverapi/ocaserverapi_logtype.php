<?php

class OCAServerApi_LogType extends OCAServerApi_EntityType {
	public $singular = 'logtype';
	public $plural = 'logtypes';

    /**
     * Creates an logs of this type
     *
     * @param array $data The data to create the log with
     * @returns string The URL of the newly created log
     */
	public function create($data) {
        $data['TypeURL'] = $this->url;
        $data['FieldData'] = $this->fieldsToIds($data['FieldData']);
        return $this->_create('/api/v1/logs', $data);
	}

    static public function Search($client, $params) {
        return parent::_Search($client, '/api/v1/logtypes', $params);
    }
}
