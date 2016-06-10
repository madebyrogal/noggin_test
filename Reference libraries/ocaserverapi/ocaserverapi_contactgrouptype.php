<?php

class OCAServerApi_ContactGroupType extends OCAServerApi_Entity {
	public $singular = 'contactgrouptype';
	public $plural = 'contactgrouptypes';

    /**
     * Creates an contact group of this type
     *
     * @param array $data The data to create the contact group with
     * @returns string The URL of the newly created contact group
     */
	public function create($data) {
        $data['TypeURL'] = $this->url;
        $data['FieldData'] = $this->fieldsToIds($data['FieldData']);
        return $this->_create('/api/v1/contactgroups', $data);
	}

    protected function fieldMap() {
        $fields = array();
        foreach ($this->data['Fields'] as $fieldId => $fieldInfo) {
            $fields[$fieldId] = $fieldInfo['Label'];
        }
        return $fields;
    }

    public function fieldsToIds($fieldData) {
        $output = array();
        $map = array_flip($this->fieldMap());
        foreach ($fieldData as $key => $val) {
            if (array_key_exists($key, $map)) {
                $output[$map[$key]] = $val;
            }
        }
        return $output;
    }

    public function fieldsToLabels($fieldData) {
        $output = array();
        $map = $this->fieldMap();
        foreach ($fieldData as $key => $val) {
            if (array_key_exists($key, $map)) {
                $output[$map[$key]] = $val;
            }
        }
        return $output;
    }

    static public function Search($client, $params) {
        return parent::_Search($client, '/api/v1/contactgrouptypes', $params);
    }
}
