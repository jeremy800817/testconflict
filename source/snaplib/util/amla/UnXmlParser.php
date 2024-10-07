<?php

namespace Snap\util\amla;

use \SimpleXMLElement;
use Snap\util\amla\BaseParser;

class UnXmlParser extends BaseParser
{
    const NRICFIELD = 'National Identification Number';

    protected $app;

    protected $configs = [
        'params'            => 'name:NAME,icno:NRIC,dateofbirth:DOB,alias:ALIAS,address:ADDRESS,listedon:LISTED_ON,remarks:COMMENTS1',
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
    public function parseData($file)
    {
        try {
            // Fetch data from api and prepare the param
            $this->log(__METHOD__ . '() Fetching and formatting data from File', SNAP_LOG_DEBUG);
            return $this->applyParams($this->parseRecord(simplexml_load_file($file)));
        } catch (\Exception $e) {
            $this->log(__METHOD__ . '() Unable to parse data with error ' . $e->getMessage(), SNAP_LOG_ERROR);

            throw $e;
        }
    }

    /**
     * This method extract the xml and return each record extracted as array
     *
     * @param  SimpleXMLElement $xml
     * @return array
     */
    protected function parseRecord($xml)
    {
        $records = [];

        if (false === $xml) {
            $this->log(__METHOD__ . '() Could not read XML file', SNAP_LOG_ERROR);
            throw new \Exception('Could not read XML file');
        }

        // Encode xml into string and decode it to get array result
        $arrRecords = json_decode(json_encode($xml), true);

        if (isset($arrRecords['INDIVIDUALS']) && isset($arrRecords['INDIVIDUALS']['INDIVIDUAL'])) {

            $individuals = $arrRecords['INDIVIDUALS']['INDIVIDUAL'];

            foreach ($individuals as $individual) {

                $docs        = $individual['INDIVIDUAL_DOCUMENT'] ?? [];
                $alias       = $individual['INDIVIDUAL_ALIAS'] ?? [];
                $address     = $individual['INDIVIDUAL_ADDRESS'] ?? [];
                $dob         = $individual['INDIVIDUAL_DATE_OF_BIRTH'] ?? [];
                $nationality = $individual['NATIONALITY'];

                $name = array_filter([
                    $individual['FIRST_NAME'] ?? null,
                    $individual['SECOND_NAME'] ?? null,
                    $individual['THIRD_NAME'] ?? null,
                    $individual['FOURTH_NAME'] ?? null,
                ]);

                $individual['NAME'] = implode(' ', array_map('trim', $name));

                if ($this->isAssoc($address)) {
                    $individual['ADDRESS'] = implode(', ', array_filter($address));
                } else {
                    $individual['ADDRESS'] = implode('. ', array_map(function ($entry) {
                        return implode(', ', array_filter($entry));
                    }, $address));
                }

                if ($this->isAssoc($alias)) {
                    $individual['ALIAS'] = count($alias['ALIAS_NAME']) ? $alias['ALIAS_NAME'] : null;
                } else {
                    $individual['ALIAS'] = implode(', ', array_filter(array_map(function ($entry) {
                        return $entry['ALIAS_NAME'];
                    }, $alias)));
                }

                if ($this->isAssoc($dob)) {
                    if ('BETWEEN' === $dob['TYPE_OF_DATE']) {
                        $individual['DOB'] = $dob['FROM_YEAR'];
                    } else {
                        $individual['DOB'] = $dob['DATE'];
                    }
                } else {
                    if ('BETWEEN' === $dob[0]['TYPE_OF_DATE']) {
                        $individual['DOB'] = $dob[0]['FROM_YEAR'];
                    } else {
                        $individual['DOB'] = $dob[0]['DATE'];
                    }
                }

                if (0 < count($nationality)) {
                    if (is_array($nationality['VALUE'])) {
                        $individual['NATIONALITY'] = implode(', ', array_filter($nationality['VALUE']));
                    } else {
                        $individual['NATIONALITY'] = $nationality['VALUE'];

                    }
                } else {
                    $individual['NATIONALITY'] = $nationality['VALUE'];
                }

                if ($this->isAssoc($docs)) {
                    $individual['NRIC'] = trim(count($docs['NUMBER']) ? $docs['NUMBER'] : null);
                    $records[] = $individual;
                } elseif (empty($docs)) {
                    $individual['NRIC'] = null;
                    $records[] = $individual;
                } else {

                    // Save different id as different individual
                    // foreach ($docs as $doc) {
                    //     $individual['NRIC'] = trim(count($doc['NUMBER']) ? $doc['NUMBER'] : null);

                    //     $records[] = $individual;
                    // }

                    $individual['NRIC'] = implode(', ', array_filter(array_map(function ($entry) {
                        return $entry['NUMBER'];
                    }, $docs)));

                    $records[] = $individual;

                }
            }
        } else {
            $this->log(__METHOD__ . '() Unable to find any record', SNAP_LOG_ERROR);
            throw new \Exception('Could not find any records for individual');
        }

        return $records;
    }

    /**
     * Determines if an array is associative.
     *
     * An array is "associative" if it doesn't have sequential numerical keys beginning with zero.
     *
     * @param  array  $array
     * @return bool
     */
    public static function isAssoc(array $array)
    {
        $keys = array_keys($array);

        return array_keys($keys) !== $keys;
    }
}
