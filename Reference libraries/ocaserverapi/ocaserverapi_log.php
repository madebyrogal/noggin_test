<?php

class OCAServerApi_Log extends OCAServerApi_Entity {
	public $singular = 'log';
	public $plural = 'logs';
    protected $type;

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

	public function createSub($typeUrl, $data) {
        $data['TypeURL'] = $typeUrl;
        $data['FieldData'] = $this->client->get($typeUrl)->fieldsToIds($data['FieldData']);
        return $this->_create($this->url . '/logs', $data);
	}

	public function subs($params=array()) {
        return parent::_Search($this->client, $this->url . '/logs', $params);
	}

	public function addDocument($doc) {
        if (is_array($doc)) {
            $response = $this->client->upload($this->url . '/related/documents?filename='.urlencode($doc['name']), $doc);
            if ($response['statusCode'] == 201) {
                return $response['headers']['Location'];
            } else {
                throw new Exception('Unable to upload and relate document: ' . $doc['name'] . '  to ' . $this->url);
            }
        } else {
            $response = $this->client->call($this->url . '/related/document/' . urlencode($doc));
            if ($response['statusCode'] == 202) {
                return true;
            } else {
                throw new Exception('Unable to upload and relate document: ' . $doc . '  to ' . $this->url);
            }
        }
        return $response;
	}

	public function removeDocument($docId) {
        $response = $this->client->call('DELETE', $this->url . '/related/document/' . urlencode($docId));
        if ($response['statusCode'] == 202) {
            return true;
        }
        throw new Exception('Unable to delete related document: ' . $docId);
    }

    static public function Search($client, $params=array()) {
        return parent::_Search($client, '/api/v1/logs', $params);
    }
}
