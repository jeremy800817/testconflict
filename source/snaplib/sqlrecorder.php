<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016, 2017
//
//////////////////////////////////////////////////////////////////////

Namespace Snap;

Use Snap\TLogging;

/**
* This is a utility class to allow recording of sql queries that can later be replayed on an actually
* sql query handle.  The primary use of this class is to allow the default handler class to use the same
* where clause with 2 queries, one for select count and the other to select data out.
*
* @author  Devon Koh <devon@silverstream.my>
* @version 1.0
 * @package  snap.base
*/
class sqlrecorder
{
    Use \Snap\TLogging;
    /**
     * Default storage of all the sql instructions made
     * @var Array
     */
    private $sequences = null;

    /**
     * Checks if this recorder has any data in it.
     * @return boolean [description]
     */
    public function hasRecording()
    {
        return 0 < count($this->sequences);
    }

    /**
     * The main method to construct the query again for another sql handle
     * @param  object $target The sql handle from hydrahon
     * @return The sql handle
     */
    public function replayTo($target)
    {
        foreach ($this->sequences as $sequence) {
            $methodName = $sequence['methodName'];
            $parameters = $sequence['params'];
            if (count($parameters) && $parameters[0] instanceof sqlRecorder) {
                $subRecorder = $parameters[0];
                $target = call_user_func_array(array($target, $methodName), [ function ($q) use ($subRecorder) {
                    $subRecorder->replayTo($q);
                }]);
            } else {
                $target = call_user_func_array(array($target, $methodName), $parameters);
            }
        }
        return $target;
    }

    /**
     * Overriden method to store all the calls so that it can be reconstructed later.
     * @param  string $methodName Name of the method called
     * @param  array  $parameters Parameters used when calling the method.
     * @return $this
     */
    public function __call($methodName, $parameters)
    {
        $this->sequences[] = array( 'methodName' => $methodName, 'params' => $parameters);
        return $this;
    }
}
?>