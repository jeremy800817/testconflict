<?php 
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
namespace Snap\override\hydrahon;

use \ClanCats\Hydrahon\Query\Sql;
Use Snap\override\hydrahon\snaptable;

/**
* Extended the hydrahon package to support returning objects so that other runner methods such as sum, avg, count, get, find can run
* 
* @author  Devon Koh <devon@silverstream.my>
* @version 1.0
* @package  snap.store.override
*/
class snapSql extends Sql
{
    /**
     * Create a new table instance.  Override to use snapTable instead of the default one.
     * 
     *     $h->table('users')
     *
     * @param string|array                              $fields
     * @return Table
     */
    public function table($table = null, $alias = null)
    {
        $query = new snapTable($this); return $query->table($table, $alias);
    }
}
?>