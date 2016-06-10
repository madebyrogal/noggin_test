<?php

class OCAServerApi_ResourceStructureNode extends OCAServerApi_Entity
{
    public $singular = 'resourcestructurenode';
    public $plural = 'resourcestructuresnodes';
    protected $type;

    public function __construct($client, $url)
    {
        $this->client = $client;
        if (!empty($url)) {
            $this->url = $url;
            $rawData = $this->client->call('GET', $this->url);
            if ($rawData['statusCode'] == 200 && array_key_exists(0, $rawData['payload'])) {
                $this->data = $rawData['payload'][0];
            }
        }
    }

    public function delete() {
        return $this->_delete();
    }

    /**
     * Get the resource structure nodes the current OCA User can be allocated into
     *
     * @param $params Dynamic fields to include in the result set
     * @return array
     */
    static public function getMyPossibleNodes($client,$params)
    {
        $response = $client->call('GET', '/api/v2/me/possibleresourcestructurenodes?' . http_build_query($params));
        return $response['payload'];
    }

    /**
     * Get the resource structure nodes the current OCA User is allocated inot
     *
     * @param $params Dynamic fields to include in the result set
     * @return array
     */
    static public function getMyCurrentNodes($client,$params)
    {
        $response = $client->call('GET', '/api/v2/me/currentresourcestructurenodes?' . http_build_query($params));
        return $response['payload'];
    }

    /**
     * Search all resource structure nodes
     *
     * @param $client OCAServerApi_Client
     * @param array $params
     * @return mixed
     */
    static public function Search($client, $params = array())
    {
        return parent::_Search($client, '/api/v2/resourcestructurenodes', $params);
    }

    /**
     * Fill all available allocations for a resource structure node
     */
    public function fill() {
        return $this->client->call('POST', $this->url . '/fill');
    }

    /**
     * Retrieves a list of contacts who CAN allocate into a resource structure node
     */
    public function getPossibleAllocatees() {
        $result = $this->client->call('GET', $this->url . '/possibleallocatees');
        return $result['payload'];
    }

    /**
     * Retrieve a list of candidates and candidate scores for a resource structure node
     *
     * @param array $params
     * @return array Result set containing the payload and pagination details
     */
    public function getCandidates($params = array()) {
        $result = $this->client->call('GET', $this->url . '/candidates?' . http_build_query($params));
        return $result;
    }

    /**
     * Bulk update the status of this resource structure node's object resource allocations
     *
     * @param $status
     */
    public function bulkUpdateStatusOnObjectResourceAllocations($status) {
        return $this->client->call('PUT', $this->url . '/objectresourceallocations', array('Status' => $status));
    }

    /**
     * Get resource allocations in a resource structure node
     *
     * @param $params
     * @return array Resource allocations
     */
    public function getResourceAllocations($params = array()) {
        return $this->client->call('GET', $this->url . '/objectresourceallocations?' . http_build_query($params));
    }

    /**
     * Get resource allocations in a resource structure node
     *
     * @param $params
     * @return array Resource allocations
     */
    public function getPotentialShifts($params = array()) {
        $result = $this->client->call('GET', $this->url . '/potentialshifts?' . http_build_query($params));
        return $result['payload'];
    }

    /**
     * Add object resource allocations into a resource structure node
     *
     * @param $data
     * @return mixed
     * @throws Exception
     */
    public function addResourceAllocation($data) {
        $response = $this->client->call('POST', $this->url . '/objectresourceallocations', $data);
        if ($response['statusCode'] == 201) {
            return $response['headers']['Location'];
        } else {
            throw new Exception('Unable to add resource to the resource structure node: ' . $data['URL'] . '  to ' . $this->url);
        }
    }


}
