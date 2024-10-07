<?php

namespace Snap\util\amla;

use Smalot\PdfParser\Parser;
use Snap\util\amla\BaseParser;

class MohaPdfParser extends BaseParser
{
    protected $parser;

    protected $configs = [
        'params'            => 'name:name,icno:nationalidentificationno,dateofbirth:dateofbirth,alias:alias,address:address,listedon:datelisted,remarks:otherinformation',
        'row_regex'         => '/KDN\.I.(?:(?!KDN)[\s\S])+/',
        'field_regex'       => '/(Name|Title|Designation|Date of Birth|Alias|Other Names|Place of Birth|Nationality|Passport No|National Identification No|Address|Date Listed|Other Information|)[ ]*:[ ]*(.*?(?=\sName|Title|Designation|Date of Birth|Alias|Other Names|Place of Birth|Nationality|Passport No|National Identification No|Address|Date Listed|Other Information|:|$))/',
        'empty_placeholder' => 'n/a',
    ];

    public function __construct($configs = [])
    {
        $this->parser   = new Parser;
        $this->configs  = array_merge($this->configs, $configs);
    }

    /**
     * Parse the data from the PDF and return the data as array
     *
     * @return array
     */
    public function parseData($file)
    {
        try {
            // Fetch data from api and prepare the param
            $this->log(__METHOD__ . "() Fetching and formatting data ", SNAP_LOG_DEBUG);
            $text = $this->parser->parseFile($file)->getText();

            return $this->applyParams($this->parseRecord($this->cleanText($text)));
        } catch (\Exception $e) {

            $this->log(__METHOD__ . "() Unable to parse data with error " . $e->getMessage(), SNAP_LOG_ERROR);
            // Return empty array

            throw $e;
            // return [];
        }
    }

    /**
     * Clean the text parsed from pdf
     *
     * @param  string $text
     * @return string
     */
    protected function cleanText($text)
    {
        $text = nl2br($text); // Paragraphs and line break formatting
        $text = str_replace(array('-', '–'), '-', $text); // Check special characters
        $text = preg_replace('/[\x00-\x1F\x80-\xFF]/', '', $text);
        $text = str_replace(array("<br /> <br /> <br />", "<br> <br> <br>"), "<br /> <br />", $text); // Optional
        $text = addslashes($text); // Backslashes for single quotes
        $text = stripslashes($text);
        $text = strip_tags($text);
        $text = preg_replace('/\s+/', ' ', $text); // Remove extra whitespace

        return $text;
    }

    /**
     * This method extract the text into rows and extract the field and value
     * of each row and return each record extracted as array
     *
     * @param  string $text
     * @return array
     */
    protected function parseRecord($text)
    {
        $records = [];

        // Extract data into rows first
        preg_match_all($this->configs['row_regex'], $text, $allRows, PREG_SET_ORDER);

        foreach ($allRows as $aRow) {

            // Extract data into key value for each row
            if (preg_match_all($this->configs['field_regex'], $aRow[0], $allFields, PREG_SET_ORDER)) {

                $record = [];

                // Populate
                foreach ($allFields as $aField) {

                    // Lowercase the field name
                    $key = preg_replace('/\s+/', '', strtolower($aField[1]));
                    $value = trim($aField[2]);
                    $record[$key] = $value;
                }

                // Add record
                $records[] = $record;
            }
        }

        return $records;
    }
}
