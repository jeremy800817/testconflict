<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2016 - 2018
 * @copyright Silverstream Technology Sdn Bhd. 2016 - 2018
 */
Namespace Snap\store;

Use Snap\object\user;
Use PDO;
use PDOException;
use PDORow;

/**
* This class implements a basic data storage service that will persist the data into database.  It supports
* operations that deals with single table and is associated directly with an IEntity item interface.  This data
* store will also support views etc as in the old interface in mxObject::getAll()
*
* @author  Weng <wayne@silverstream.my>
* @version 1.0
* @package  snap.store
*/

class userstore extends dbdatastore
{
}
?>