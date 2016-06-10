<?php

class OCAServerApi_ContactType extends OCAServerApi_Entity {
	public $singular = 'contacttype';
	public $plural = 'contacttypes';

    /**
     * Creates a contact of this type
     *
     * @param array $data The data to create the contact with
     * @returns string The URL of the newly created contact
     */
	public function create($data) {
        throw new Exception('Contacts must be created via the contact group using createContact($typeUrl, $data)');
        /*$data['TypeURL'] = $this->url;
        $data['FieldData'] = $this->fieldsToIds($data['FieldData']);
        return $this->_create('/api/v1/contacts', $data);*/
	}

    /**
     * Get the field data labels mapped to IDs
     * @return type
     */
    protected function fieldMap() {
        $fields = array();
        // Tabs (top section)
        foreach ($this->data['Tabs'] as $tabIndex => $tab) {
            $fields['Tabs'][$tabIndex] = $tab['Name'];
        }
        foreach ($this->data['TabFields'] as $tabIndex => $tab) {
            foreach ($tab as $fieldId => $fieldInfo) {
                $fields['TabFields'][$tabIndex][$fieldId] = $fieldInfo['Label'];
            }
        }
        // Profiles (lower section)
        foreach ($this->data['Profiles'] as $profileIndex => $profile) {
            $fields['Profiles'][$profileIndex] = $profile['label'];
        }
        foreach ($this->data['ProfileFields'] as $profileIndex => $profile) {
            $fields['ProfileFields'][$profileIndex] = $profile['Label'];
        }
        return $fields;
    }

    /**
     * Get the field data IDs mapped to labels
     * @return type
     */
    protected function flipFieldMap() {
        $fields = array();
        // Tabs (top section)
        foreach ($this->data['Tabs'] as $tabIndex => $tab) {
            $fields['Tabs'][$tab['Name']] = $tabIndex;
        }
        foreach ($this->data['TabFields'] as $tabIndex => $tab) {
            foreach ($tab as $fieldId => $fieldInfo) {
                $fields['TabFields'][$fieldInfo['Label']] = $fieldId;
            }
        }
        // Profiles (lower section)
        foreach ($this->data['Profiles'] as $profileIndex => $profile) {
            $fields['Profiles'][$profile['label']] = $profileIndex;
        }
        foreach ($this->data['ProfileFields'] as $profileIndex => $profile) {
            $fields['ProfileFields'][$profile['Label']] = $profileIndex;
        }
        return $fields;
    }

    /**
     * Convert field data to IDs
     *
     * @param type $fieldData
     * @return type
     * @throws Exception
     */
    public function fieldsToIds($fieldData) {
        $output = array();
        $map = $this->flipFieldMap();
        foreach ($fieldData as $type => $typeData) {
            switch ($type) {
                case 'BaseFieldData':
                    foreach($typeData as $key => $value) {
                        $output['BaseFieldData'][$map['TabFields'][$key]] = $value;
                    }
                    break;
                case 'ProfileFieldData':
                    $profileTabIndex = 1;
                    foreach($typeData as $key => $value) {
                        foreach($value AS $subTypeKey => $subTypeValue) {
                            $output['ProfileFieldData'][$profileTabIndex][$map['ProfileFields'][$subTypeKey]] = $subTypeValue;
                        }
                        $profileTabIndex++;
                    }
                    break;
                default:
                    // Removed the exception as new types may be added, and old stuff needs to work
                    // Unknown - return as-is
                    $output[$type] = $typeData;
                    break;
            }
        }
        return $output;
    }

    public function fieldsToLabels($fieldData) {
        $output = array();
        $map = $this->fieldMap();
        foreach ($fieldData as $type=>$typeData) {
            switch ($type) {
                case 'BaseFieldData':
                    foreach ($map['Tabs'] as $tabID=>$tabLabel) {
                        // Puts all tab fields into base fields
                        if (array_keypath_exists($map, 'TabFields', $tabID))
                        foreach ($map['TabFields'][$tabID] as $key=>$value) {
                            if (array_keypath_exists($typeData, 'BaseFieldData', $key)) {
                                $output['BaseFieldData'][$value] = $typeData['BaseFieldData'][$key];
                            } else {
                                $output['BaseFieldData'][$value] = '';
                            }
                        }
                    }

                    foreach ($typeData as $key=>$val) {
//                        if (is_array($val)) {
//                            // Die - this should not be an array - unhandled case
//                            throw new Exception('Unhandled contact data case: Base field should data should not be an array');
//                            return;
//                        }

                        foreach ($map['Tabs'] as $tabID=>$tabLabel) {
                            if (array_keypath_exists($map, 'TabFields', $tabID, $key)) {
                                $output['BaseFieldData'][$map['TabFields'][$tabID][$key]] = $val;
                            }
                        }
                    }

                    foreach ($map['Profiles'] as $key=>$val) {
                        foreach ($map['ProfileFields'] as $fieldID=>$fieldLabel) {
                            if (array_keypath_exists($typeData, $key, $fieldID)) {
                                $output['ProfileFieldData'][$val][$fieldLabel] = $typeData[$key][$fieldID];
                            } else {
                                $output['ProfileFieldData'][$val][$fieldLabel] = '';
                            }
                        }
                    }
                    break;
                case 'ProfileFieldData':
                    foreach ($map['Profiles'] as $key=>$val) {
                        foreach ($map['ProfileFields'] as $fieldID=>$fieldLabel) {
                            if (array_keypath_exists($typeData, $key, $fieldID)) {
                                $output['ProfileFieldData'][$val][$fieldLabel] = $typeData[$key][$fieldID];
                            } else {
                                $output['ProfileFieldData'][$val][$fieldLabel] = '';
                            }
                        }
                    }
                    break;
                default:
                    // Removed the exception as new types may be added, and old stuff needs to work
                    // Unknown - return as-is
                    $output[$type] = $typeData;
                    break;
            }
        }

        return $output;
    }

    static public function Search($client, $params) {
        return parent::_Search($client, '/api/v1/contacttypes', $params);
    }

    /**
     * Get the lists for contact types
     * Note: $contacttype as TabField key was removed,
     * as the contact type did not match a TabField key
     *
     * @staticvar null $output
     * @return type
     */
    public function Lists() {
        $output = array();
        foreach ($this->data['TabFields'] as $tabId => $tabInfo) {
            foreach ($tabInfo as $fieldId => $fieldInfo) {
                if ($fieldInfo['Type'] == 'opt') {
                    $output[$fieldInfo['Label']] = array();
                    foreach ($fieldInfo['Options'] as $option) {
                        $output[$fieldInfo['Label']][] = array('value' => $option[0], 'label' => $option[1]);
                    }
                } else if ($fieldInfo['Type'] == 'subform') {
                    $output[$fieldInfo['Label']] = array();
                    foreach ($fieldInfo['SubFields'] as $subFieldId => $subFieldInfo) {
                        if ($subFieldInfo['Type'] == 'opt') {
                            $output[$fieldInfo['Label']][$subFieldInfo['Label']] = array();
                            foreach ($subFieldInfo['Options'] as $option) {
                                $output[$fieldInfo['Label']][$subFieldInfo['Label']][] = array('value' => $option[0], 'label' => $option[1]);
                            }
                        }
                    }
                }
            }
        }

        foreach ($this->data['ProfileFields'] as $fieldId => $fieldInfo) {
            if ($fieldInfo['Type'] == 'opt') {
                $output[$fieldInfo['Label']] = array();
                foreach ($fieldInfo['Options'] as $option) {
                    $output[$fieldInfo['Label']][] = array('value' => $option[0], 'label' => $option[1]);
                }
            } else if ($fieldInfo['Type'] == 'subform') {
                $output[$fieldInfo['Label']] = array();
                foreach ($fieldInfo['SubFields'] as $subFieldId => $subFieldInfo) {
                    if ($subFieldInfo['Type'] == 'opt') {
                        $output[$fieldInfo['Label']][$subFieldInfo['Label']] = array();
                        foreach ($subFieldInfo['Options'] as $option) {
                            $output[$fieldInfo['Label']][$subFieldInfo['Label']][] = array('value' => $option[0], 'label' => $option[1]);
                        }
                    }
                }
            }
        }

		return $output;
    }

    /**
     * Extract the required fields from the field info stored in the type
     *
     * @staticvar null $output
     * @return type
     */
    public function Required() {
        static $output = null;
        if ($output === null) {
            $output = array();
            foreach ($this->data['TabFields'] as $tabId => $tabInfo) {
                foreach ($tabInfo as $fieldId => $fieldInfo) {
                    $output[$fieldInfo['Label']] = ($fieldInfo['Required'])?1:0;
                }
            }
        }
		return $output;
    }
}
