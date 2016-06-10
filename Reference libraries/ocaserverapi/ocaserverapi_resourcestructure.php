<?php

class OCAServerApi_ResourceStructure extends OCAServerApi_Entity {
	public $singular = 'resourcestructure';
	public $plural = 'resourcestructures';
    protected $type;

    static public function Search($client, $params=array()) {
        return parent::_Search($client, '/api/v2/resourcestructures', $params);
    }
}
