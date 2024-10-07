<?php

namespace Snap\util\amla;

use Exception;
use Snap\TLogging;
use Snap\App;
use Snap\util\amla\BaseParser;

class BnmJsonParser extends BaseParser
{
    use TLogging;

    protected $app;
    protected $url;

    protected $configs = [
        'url'               => 'https://api.bnm.gov.my/public/consumer-alert',
        'params'            => 'name:name,businessregno:regisrationnumber,listedon:added_date',
        'version'           => 'application/vnd.BNM.API.v1+json',
        'empty_placeholder' => '',
    ];

    public function __construct($configs = [])
    {
        $this->configs  = array_merge($this->configs, $configs);
    }

    /**
     * Parse the data from the API and return the data as array
     *
     * @return array
     */
    public function parseData($fileOrUrl, $isApi = true)
    {
        try {
           
            if ($isApi) {
                $this->log(__METHOD__ . "() Fetching and formatting data from API", SNAP_LOG_DEBUG);
                $records = $this->applyParams($this->fetchData($fileOrUrl));
            } else {
                $this->log(__METHOD__ . "() Fetching and formatting data from File", SNAP_LOG_DEBUG);
                $records = $this->applyParams($this->fetchDataFromFile($fileOrUrl));
            }

            return $records;
        } catch (\Throwable $th) {
            $this->log(__METHOD__ . "() Unable to parse data with error " . $th->getMessage(), SNAP_LOG_ERROR);

            // Return empty array
            // return [];

            throw new \Exception('Unable to parse data');
        }
    }

    /**
     * Read data from file and return the records as array
     *
     * @param  string $file
     * @return array
     */
    protected function fetchDataFromFile($file)
    {
        $contents = file_get_contents($file);
        $arrData  = json_decode($contents, true);

        if (is_null($arrData)) {
            throw new Exception("Unable to parse data.");
        }

        return $arrData['data'];
    }

    /**
     * Fetch the data from the API
     *
     * @return array
     */
    protected function fetchData($url = null)
    {
        try {

            $client = new \GuzzleHttp\Client([
                'verify' => false,
                'headers' => [
                    'Accept' => $this->configs['version'],
                    'User-Agent' => 'GuzzleHttp', // Put anything. Needed else BNM API will return error
                ],
            ]);

            $response = $client->request('GET', $url ?? $this->configs['url']);

            if (200 == $response->getStatusCode()) {
                $responseBody = $response->getBody()->getContents();
                return json_decode($responseBody, true)['data'];
            } else {
                $this->log(__METHOD__ . "() Something went wrong. Response status code: " . $response->getStatusCode(), SNAP_LOG_ERROR);
            }
        } catch (\GuzzleHttp\Exception\RequestException $e) {
            $responseBody = 'Exception Caught: ' . $e->getResponse()->getBody(true);
            $this->log(__METHOD__ . "() Unable to connect to BNM API with error " . $e->getMessage() . "\nResponse:" . $responseBody, SNAP_LOG_ERROR);

            throw new \Exception($e->getMessage());
        }
    }
}
