<?php

namespace Snap\util\amla;

use Snap\TLogging;

abstract class BaseParser
{
    use TLogging;

    abstract public function parseData($file);

    /**
     * Apply the params and assign value to it from the key specified in the config
     *
     * @param  array $records
     * @return array
     */
    protected function applyParams(array $records)
    {
        $params = explode(',', $this->configs['params']);

        foreach ($records as &$record) {
            foreach ($params as $param) {
                list($replace, $current) = explode(':', $param);
                if (is_array($this->configs['empty_placeholder'])) {
                    $record[$replace] = in_array($record[$current], $this->configs['empty_placeholder']) ? null : $record[$current];
                } else {
                    $record[$replace] = $this->configs['empty_placeholder'] == $record[$current] ? null : $record[$current];
                }
            }
        }

        return $records;
    }
}
