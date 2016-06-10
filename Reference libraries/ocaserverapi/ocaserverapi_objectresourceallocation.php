<?php

/**
 * Class OCAServerApi_ObjectResourceAllocation
 *
 * Interact with Object Resource Allocations in  OCA
 * An Object Resource Allocation is the object representing the relationship between the actual Contact / Asset and the
 * Resource Structure Node on the Resource Assignment tree
 *
 * This class allows
 */
class OCAServerApi_ObjectResourceAllocation extends OCAServerApi_Entity {

    protected $type;
    public $url;

    public function type() {
        if (!isset($this->type)) {
            $this->type = $this->client->get($this->data['TypeURL']);
        }
        return $this->type;
    }

    public function change($data) {
        $data['FieldData'] = $this->type()->fieldsToIds($data['FieldData']);
        return $this->_change($data);
    }

    public function delete() {
        return $this->_delete();
    }

    public function create($data) {
        throw new Exception('Not yet implemented');
    }

    public function get($url) {
        return $this->_get($this->client, $url);
    }

}
