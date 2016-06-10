<?php

class OCAServerApi_ObjectResourceAllocationType extends OCAServerApi_EntityType {
    public $singular = 'objectresourceallocationtype';
    public $plural = 'objectresourceallocationtypes';

    public function create($data) {
        throw new Exception('Not implemented');
    }

    static public function Search($client, $params) {
        throw new Exception('Not implemented');
    }
}
