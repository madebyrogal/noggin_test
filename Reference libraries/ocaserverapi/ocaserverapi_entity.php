<?php

abstract class OCAServerApi_Entity implements OCAServerApi_Entity_Interface {

    public $client;
    public $singular;
    public $plural;
    public $url;
    public $data;

    public function __construct($client, $url = null) {
        $this->client = $client;
        if (!empty($url)) {
            $this->url = $url;
            $rawData = $this->client->call('GET', $this->url);
            if ($rawData['statusCode'] == 200 && array_key_exists(0, $rawData['payload'])) {
                $this->data = $rawData['payload'][0];
            }
        }
    }

    static protected function _Search($client, $url, $params) {
        $response = $client->call('GET', $url . '?' . http_build_query($params));
        return $response['payload'];
    }

    protected function _query($client, $url, $params) {
        $response = $client->call('GET', $url . '?' . http_build_query($params));
        return $response;
    }

    protected function _get($client, $url) {
        $response = $client->call('GET', $url);
        return $response;
    }

    protected function _change($data) {
        $response = $this->client->call('PUT', $this->url, $data);
        if ($response['statusCode'] >= 200 && $response['statusCode'] <= 299) {
            return true;
        } else {
            throw new Exception('Unable to change ' . $this->url . ': ' . $response['statusCode']);
        }
    }

    /**
     *
     * @param string $url The type url on the OCA API
     * @param array $data The array in ID => value format
     * @return array The response from the OCA API
     * @throws Exception
     */
    protected function _create($url, $data) {
        $response = $this->client->call('POST', $url, $data);
        if ($response['statusCode'] >= 200 && $response['statusCode'] <= 299) {
            return $response['headers']['Location'];
        } else {
            throw new Exception('Unable to create ' . $url . ': ' . $response['statusCode']);
        }
    }

    protected function _delete() {
        $response = $this->client->call('DELETE', $this->url);
        if ($response['statusCode'] >= 200 && $response['statusCode'] <= 299) {
            return true;
        } else {
            throw new Exception('Unable to delete ' . $this->url . ': ' . $response['statusCode']);
        }
    }

}
