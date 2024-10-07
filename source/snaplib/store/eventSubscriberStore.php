<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2016 - 2018
 * @copyright Silverstream Technology Sdn Bhd. 2016 - 2018
 */
Namespace Snap\store;

Use Snap\IEntityStore;
Use Snap\IEntity;
Use Snap\App;
Use Snap\store\dbDatastore as dbDatastore;
Use Snap\Object\SnapObject;
/**
* This class implements a basic data storage service that will persist the data into database.  It supports
* operations that deals with single table and is associated directly with an IEntity item interface.  This data
* store will also support views etc as in the old interface in mxObject::getAll()
*
* @author  Devon Koh <devon@silverstream.my>
* @version 1.0
* @package  snap.store
*/
class eventSubscriberStore extends dbdatastore
{
    /**
     * Adds a new subscriber to the specified event.
     * @param int     $evtmoduleid    module id
     * @param string  $evtmoduledesc  module description
     * @param int     $evtactionid    action id
     * @param int     $receiver       receiver
     *
     * @return eventSubscriber
    */
    public function registerSubscriber($objectid, $triggerid, $groupid, $receiver)
    {
        if (0 < $objectid) {
            $eventsubscriber = $this->getById($objectid);
            $eventsubscriber->receiver = $receiver;
            $eventsubscriber->status = SnapObject::STATUS_ACTIVE;
        } else {
            $eventsubscriber = $this->create();
            $eventsubscriber->triggerid = $triggerid;
            $eventsubscriber->groupid = $groupid;
            $eventsubscriber->receiver = $receiver;
            $eventsubscriber->status = SnapObject::STATUS_ACTIVE;
        }
        return $eventsubscriber;
    }
}
?>