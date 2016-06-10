<?php

class OCAServerApi_ReportType extends OCAServerApi_EntityType {
	public $singular = 'reporttype';
	public $plural = 'reporttypes';

    /**
     * Creates an Report of this type
     *
     * @param array $data The data to create the Report with
     * @returns string The URL of the newly created Report
     */
    public function create($data) {
        $data['TypeURL'] = $this->url;
        $data['FieldData'] = $this->fieldsToIds($data['FieldData']);
        return $this->_create('/api/v1/reports', $data);
    }

    static public function Search($client, $params) {
        return parent::_Search($client, '/api/v1/reporttypes', $params);
    }
}
