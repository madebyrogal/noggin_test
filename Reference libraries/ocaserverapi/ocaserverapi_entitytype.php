<?php

abstract class OCAServerApi_EntityType extends OCAServerApi_Entity {
    
    protected $lists = null;
    protected $required = null;

    public function fieldMap() {
        $fields = array();
        foreach ($this->data['Fields'] as $fieldId => $fieldInfo) {
            $fields[$fieldId] = str_replace('/', '{{FORWARDSLASH}}', $fieldInfo['Label']);
        }
        return $fields;
    }

    public function fieldsToIds($fieldData) {
        $output = array();
        $map = array_flip($this->fieldMap());
        $errors = array();
        foreach ($fieldData as $key => $val) {
            if (array_key_exists($key, $map)) {
                switch ($this->data['Fields'][$map[$key]]['Type']) {
                	case 'subform':
                		$output[$map[$key]] = array();
                        // Only submit subform values if subform rows are posted
                        if (is_array($val)) {
                            foreach ($val as $subVal) {
                                $subOutput = array();
                                foreach ($this->data['Fields'][$map[$key]]['SubFields'] as $subKey => $subInfo) {
                                    $tmp = str_replace('/', '{{FORWARDSLASH}}', $subInfo['Label']);
                                    if (array_key_exists($tmp, $subVal)) {
                                        $subOutput[$subKey]= $subVal[$tmp];
                                    }
                                }
                                $output[$map[$key]][] = $subOutput;
                            }
                        }
                        break;
                    case 'assetchooser':
                        $output[$map[$key]] = array(array('url' => $val));
                        break;
                    default:
                        $output[$map[$key]] = $val;
                }
            } else {
            	$errors[] = 'Unknown field: ' . $key;
            }
        }
        if (!empty($errors)) throw new OCAServerApi_Exception($errors);
        return $output;
    }

    public function fieldsToLabels($fieldData) {

        $output = array();
        $map = $this->fieldMap();
        $errors = array();
        foreach ($fieldData as $key => $val) {
            if (array_key_exists($key, $map)) {
                switch ($this->data['Fields'][$key]['Type']) {
                	case 'subform':
						$output[$map[$key]] = array();
						foreach ((array)$val as $subVal) {
							$subOutput = array();
							foreach ($this->data['Fields'][$key]['SubFields'] as $subKey => $subInfo) {
								$subOutput[str_replace('/', '{{FORWARDSLASH}}', $subInfo['Label'])]= $subVal[$subKey];
							}
							$output[$map[$key]][] = $subOutput;
						}
                        break;
                    case 'assetchooser':
                        // single or multi select
                        if (is_array($val)) {
                            if (array_key_exists('url', $val)) {
                                $output[$map[$key]] = $val['url'];
                            } else {
                                foreach($val as $subkey => $subval) {
                                    $output[$map[$key]][] = $subval['url'];
                                }
                            }
                        }
                        break;
                    case 'contactchooser':
                        // single or multi select
                        if (is_array($val)) {
                            if (array_key_exists('url', $val)) {
                                $output[$map[$key]] = $val['url'];
                            } else {
                                foreach($val as $subkey => $subval) {
                                    $output[$map[$key]][] = $subval['url'];
                                }
                            }
                        }
                        break;
                    default:
                        $output[$map[$key]] = $val;
                }
            } else {
            	$errors[] = 'Unknown field: ' . $key;
            }
        }
        if (!empty($errors)) throw new OCAServerApi_Exception($errors);
        return $output;
    }

    public function listValue($key, $fieldLabel, $subFieldLabel=null) {
        $lists = $this->lists();
        $tmp = $lists[$fieldLabel];
        if ($subFieldLabel !== null) $tmp = $tmp[$subFieldLabel];
        foreach ($tmp as $option) {
            if ($key == $option['value']) return $option['label'];
        }
        return null;
    }

    public function Lists() {
        if ($this->lists === null) {
            $this->lists = array();
            foreach ($this->data['Fields'] as $fieldId => $fieldInfo) {
                if ($fieldInfo['Type'] == 'opt' || $fieldInfo['Type'] == 'assetchooser' || $fieldInfo['Type'] == 'contactchooser') {
                    $this->Options($fieldInfo);
                } else if ($fieldInfo['Type'] == 'subform') {
                    $this->lists[$fieldInfo['Label']] = array();
                    foreach ($fieldInfo['SubFields'] as $subFieldId => $subFieldInfo) {
                        if ($subFieldInfo['Type'] == 'opt') {
                            $this->lists[$fieldInfo['Label']][$subFieldInfo['Label']] = array();
                            foreach ($subFieldInfo['Options'] as $option) {
                                $this->lists[$fieldInfo['Label']][$subFieldInfo['Label']][] = array('value' => $option[0], 'label' => $option[1]);
                            }
                        }
                    }
                }
            }
        }
	return $this->lists;
    }
    
    public function Options($fieldInfo) {
        if(isset($fieldInfo['Options'])) {

            $this->lists[$fieldInfo['Label']] = array();
            foreach ($fieldInfo['Options'] as $key => $option) {
                if (is_array($option)) {
                    if (isset($option['URL']) && isset($option['Name'])) {
                        $this->lists[$fieldInfo['Label']][] = array('value' => $option['URL'], 'label' => $option['Name']);
                    } else {
                        $this->lists[$fieldInfo['Label']][] = array('value' => $option[0], 'label' => $option[1]);
                    }
                } else {
                    $this->lists[$fieldInfo['Label']][] = array('value' => $key, 'label' => $option);
                }
            }
        }
    }
    
    public function Required() {
        if ($this->required === null) {
            $this->required = array();
            foreach ($this->data['Fields'] as $fieldId => $fieldInfo) {
                $this->required[$fieldInfo['Label']] = ($fieldInfo['Required'])?1:0;
            }
        }
        return $this->required;
    }    

}
