<?php 
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap\override\hydrahon;
Use \ClanCats\Hydrahon\Query\Sql;
Use \ClanCats\Hydrahon\Query\Sql\Table;
Use Snap\override\hydrahon\snapselect as snapSelect;

/**
* Extended the hydrahon package to support returning objects so that other runner methods such as sum, avg, count, get, find can run
* 
* @author  Devon Koh <devon@silverstream.my>
* @version 1.0
* @package  snap.store.override
*/
class SnapTable extends Table
{
    /**
     * Create a new select query builder
     * 
     *     $h->table('users')->select(['name', 'age'])
     *
     * @param string|array                              $fields
     * @return Select
     */
    public function select($fields = null)
    {
        $query = new snapSelect($this); return $query->fields($fields);
    }
}
?>