<?php 
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap\override\hydrahon;

use \ClanCats\Hydrahon\BaseQuery;
Use \ClanCats\Hydrahon\Query\Sql;
Use \ClanCats\Hydrahon\Query\Sql\Select;
use \ClanCats\Hydrahon\Query\Expression;
Use Snap\object\SnapObject;

/**
* Extended the hydrahon package to support returning objects so that other runner methods such as sum, avg, count, get, find can run
* 
* @author  Devon Koh <devon@silverstream.my>
* @version 1.0
* @package  snap.store.override
*/
class SnapSelect extends Select {

   /**
     * Executes the `executeResultFetcher` callback and handles the results.  Support snapObject results
     * 
     * @return mixed The fetched result.
     */
    public function get() {
         // run the callbacks to retirve the results
        $results = $this->executeResultFetcher();

        // we always exprect an array here!
        if (!is_array($results) || empty($results)) {
            $results = array();
        }

        // In case we should forward a key means using a value
        // from every result as array key.
        if ((!empty($results)) && $this->forwardKey !== false && is_string($this->forwardKey)) {
            $rawResults = $results;
            $results = array();

            // check if the collection is beeing fetched 
            // as an associated array 
            if (!is_array(reset($rawResults)) && !reset($rawResults) instanceof SnapObject) {
                throw new \Exception('Cannot forward key, the result is no associated array.');
            }

            foreach ($rawResults as $result) {
                if($result instanceof SnapObject) $results[$result->{$this->forwardKey}] = $result;
                else $results[$result[$this->forwardKey]] = $result;
            }
        }

        // Group the resuls by a items value
        if ((!empty($results)) && $this->groupResults !== false && is_string($this->groupResults)) {
            $rawResults = $results;
            $results = array();

            // check if the collection is beeing fetched 
            // as an associated array 
            if (!is_array(reset($rawResults)) && !reset($rawResults) instanceof SnapObject) {
                throw new \Exception('Cannot forward key, the result is no associated array.');
            }

            foreach ($rawResults as $key => $result) 
            {
                if($result instanceof SnapObject) $results[$result->{$this->groupResults}][$key] = $result;
                else $results[$result[$this->groupResults]][$key] = $result;
            }
        }

        // when the limit is specified to exactly one result we
        // return directly that one result instead of the entire array
        if ($this->limit === 1) {
            $results = reset($results);
        }
        return $results;
    }
}
?>