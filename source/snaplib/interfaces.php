<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2016 - 2018
 * @copyright Silverstream Technology Sdn Bhd. 2016 - 2018
 */
Namespace Snap;

/**
 * This file will list down all the interfaces that will be used by the SNAP application framework to integrate
 * with the actual application specific data and entities.
 * @author Devon Koh <devon@silverstream.my>
 * @package  snap.interface
 */

interface IHandler
{

    /**
     * This method will return the rights that are applicable for this handler with this particular user type
     *
     * @param  String  $action  Action requested by user
     * @return String   The permission string representing the permissions to check for
     */
    public function getRights($action);

    /**
     * This method will determine is this particular handler is able to handle the action given.
     *
     * @param  App    $app    The application object (for getting user session etc to test?)
     * @param  String $action The action name to be handled
     * @return boolean         True if this handler is able to response to the particular action.  False otherwise.
     */
    public function canHandleAction($app, $action);

    /**
     * This method adds in additional handler to form a composite handler chain that is able to
     * perform certain types of actions.
     *
     * @param IHandler $child The handler that would be added into.
     */
    public function addChild(IHandler $child);

    /**
    /**
     * This is the main method that will be used to handle any requests from the handler
     *
     * @param  App 	  $app    The application instance
     * @param  String $action The action (string) to operate on
     * @param  Array  $params Query parameters parsed in
     * @return void
     */
    public function doAction($app, $action, $params);
}

/**
 * Controller interface will define the interface that an application specific controller will need to implement to be
 * integrated together in this framework.
 * @author Devon Koh <devon@silverstream.my>
 * @package  snap.interface
 */
interface IAppController
{
    /**
     * This method will be called everytime the appplication framework starts up to allow proper initialisation of the
     * controller.  The initialisation can further take in the application context or mode as defined.
     *
     * @param  int $contextOrMode  The currently running mode of the application
     * @return void
     */
    public function initialiseController($app, $contextOrMode);

    /**
     * This method will be called at the end in preparation of ending the application processes
     * @param  int $contextOrMode  The currently running mode of the application
     * @return void
     */
    public function shutdownController($contextOrMode);

    /**
     * Define here is we would like to delegate all the functions in this controller to the application object so that
     * the user can use it such as App::getInstance()->getXXXXXFromController() where getXXXXXFromController is the function
     * name defined in the class that implements this interface.
     *
     * @return boolean True if the functions here should be accessible from the app.  otherwise false.
     */
    public function isCallableFromApp();

    /**
     * This is the factory method to create a usersession object for the application to use.
     * @param  App    $app The application instance requesting for the usersession.
     * @return usersession User session object to be returned
     */
    public function createUsersession(App $app);

    /**
     * This method can be used to customise the built in request router by adding parameters to it
     *
     * @param  RequestRouter $router 			The request router object to customise on
     * @param  Int           $contextOrMode  	The application run mode or context
     * @return void
     */
    public function initialiseRequestRouter(RequestRouter $router, $contextOrMode);

    /**
     * This method should be implemented and return an array with the key representing the permission
     * key (can be multiple level by specifying a path E.g.  /root/system/user/add, /root/system/user/edit).
     * The value of the array shall be a description of the permission.
     *
     * This method can be used to initialise the rbac tables with the initial permissions.  It will also
     * be used by the user-role handler to allow for configuration of the application permission.
     */
    public function getAvailableApplicationPermission();
    
    /**
     * This method will initialise all the rights to be used in the application.  The actual data will be obtained from
     * the getPermissionControlledModules() method.  The resulting data should be cached with the app controller version
     * info or something so that changes can be easily detected and updated.
     *
     * @param  String $appVersion The application version info
     * @return [type]             [description]
     */
    // function initialiseRights( $appVersion);
}

/**
 * This interface defines a storage facility that will instantiates objects to be used.
 * @see  \Snap\store\dbdatastore              dbdatastore - provides direct database operation
 * @see  \Snap\store\simplecachedbdatastore   simplecachedbdatastore - provides ID based cached & database storage operation
 * @see  \Snap\store\querycacheddbdatastore   querycacheddbdatastore - provides query cached & database storage operation
 * @see  \Snap\store\cacheddatastore          cacheddatastore - provides a cached based storage operation
 *
 * @author Devon Koh <devon@silverstream.my>
 * @package  snap.interface
 */
interface IEntityStore
{
    /**
     * Creation method for the entity item related to this datastore
     * @param  array  $initialProperties Key-Value pair array to initialise the object with.
     * @return IEntity  created object
     */
    public function create($initialProperties = array());

    /**
     * This method will created a new record or update an existing record from the entity object.
     *
     * @param  IEntity $entityItem   Object to be updated
     * @param  array   $updateFields Selected fields to update.  Otherwise default will update all fields
     * @param  boolean $lockRecord   Whether to add a FOR UPDATE statement into the record.
     * @throws InputException if any SQL errors are found
     * @return IEntity  The updated object
     */
    public function save(IEntity $entityItem, $updateFields = array(), $lockRecord = false);

    /**
     * This method will get the object referenced by its ID.
     * @param  integer  $id              ID of the record to obtain
     * @param  array   $fields           Field names to populate with.  Default will populate everything
     * @param  boolean $forUpdate        Whether to lock the record up for updating.
     * @return IEntity                    Object found.  Object will have ID of null / 0 if not found.
     */
    public function getById($id, $fields = array(), $forUpdate = false);

    /**
     * This method will remove the storage record for the related object by its ID
     *
     * @param  IEntity $entityItem Object to remove
     * @throws InputException if any SQL errors are found
     * @return Boolean  True if successful.  False otherwise.
     */
    public function delete(IEntity $entityItem);
}

/**
 * This interface defines the entity object that will work in accordance with the IEntityStore
 * @see  \Snap\object\snapObject   Standard implementation of the base class to be inherited by all
 * @author Devon Koh <devon@silverstream.my>
 * @package  snap.interface
 */
interface IEntity
{
    /**
     * This method will return all the fields that are stored by the object. The fields are database columns info.
     * @return Array
     */
    public function getFields();

    /**
     * This method will return all the fields that are used by views connected to this object.
     * @return Array
     */
    public function getViewFields();

    /**
     * This method will ensure if the object is valid and can be saved for future use.  If the object is not in a
     * valid state, the method will return false. Otherwise it will return true.
     *
     * @return boolean True if it is a valid object.  False otherwise.
     */
    public function isValid();

    /**
     * This method is called prior to the data being updated.  This method is intended to be used by
     * the object to make any prior actions before being updated
     *
     * @return Boolean   True if update can continue.  False otherwise.
     */
    public function onPrepareUpdate();

    /**
     * This method is used to inform the object that the update has been completed.  The object can
     * perform any further post update actions as required.
     *
     * @param  IEntity $latestCopy The last copy of the object
     * @return void
     */
    public function onCompletedUpdate(IEntity $latestCopy);

    /**
     * This method is called before a delete operation is done.
     *
     * @return Boolean  True if can continune to delete the object.  False otherwise.
     */
    public function onPredelete();
}

/**
 * This interface defines a command line interface job that needs to have
 * @author Devon Koh <devon@silverstream.my>
 * @package  snap.interface
 */
interface ICLIJob
{
    /**
     * This is the main function that will run for each job.
     * @param  App    $app    The snap application class
     * @param  array  $params Parameters that are passed in from commandline with -p option
     * @return void
     */
    public function doJob($app, $params = array());

    /**
     * This method is used to display options parameter for this job.
     * @return Array of associative array of parameters.
     *         E.g.[
     *         	  'param1' => array('required' => true, 'type' => 'int', 'desc' => 'Some description'),
     *         	  'param2' => array('required' => false, 'default' => 1, type' => 'string', 'desc' => 'Some description 22222'),
     *         ]
     *         -Where [required] indicates if the params is required for the job to run.  The cli will ensure this parameter is provided
     *                [type] is the expected data type of the parameter or its valid values.
     *                [default] is the default value for the field.
     *                [desc] is the description of the parameter and what it does.
     */
    public function describeOptions();
}

/**
 * This interface defines functions that will allow an object to be cached
 * @author Devon Koh <devon@silverstream.my>
 * @package  snap.interface
 */
interface ICacheable
{
    /**
     * This method will implement serializing the object into a cacheable string for optimum storage.
     * @return String
     */
    public function toCache();

    /**
     * This method will need to implement expanding the object back to its original from the cached data provided.
     * @param  string $data The original data provided in toCache()
     * @return void
     */
    public function fromCache($data);
}

/**
 * This interface defines the function that is needed for usersession to be implemented properly
 * The SNAP framework itself have already defined and standard usersession object that have default
 * implementations for these methods.  If there are some customisation needed, please inherit from
 * the default Snap/usersession object and customised it.  You will also then need to operation the
 * application controller createUsersession() method and return your own object.
 * @author Devon Koh <devon@silverstream.my>
 * @package  snap.interface
 */
interface IUserSession
{
    /**
    * Attempts to validate and login a user
    *
    * @param string $username Name of the user, can be empty
    * @param string $password The password to be used
    * @param boolean $smartAMS Indicate whether this session is in AJAX / Smart mode
    * @param array $extraParams Extra parameters to be passed in validating/authenticating the user session
    *
    * @return boolean True if username and password is correct or session is validated already. Otherwise, it returns False.
    */
    public function login($username = '', $password = '', $extraParams = array());
    /**
    * Initialize current user session if it exists
    *
    * @return boolean True if valid user session exists. Otherwise, it returns False.
    */
    public function initSession();

    /**
    * Logout the user from this current session.
    *
    * This method will logout the user from its session.  It will also clear the session data.
    *
    * @return void
    */
    public function logout($bDestroySession = false);

    /**
    * Store personal data for this user session with a name
    *
    * @param  string $name Name of data to store under
    * @param  mixed $object Can be an object or any data type variable (including array)
    *
    * @return void
    * @see UserSession::retrieve()
    */
    public function store($name, $object);

    /**
    * Retrieve personal data for this user session by name
    *
    * @param  string $name Name of data to retrieve from
    *
    * @return mixed The stored data
    * @see UserSession::store()
    */
    public function retrieve($name);
}

/**
*  The interfaces below is used to define the observer pattern.
*  The observer interface defines the methods and class types of the object doing the observation.
*  When a notification is issued on the observable, the observer's update() function shall be called.
*
 * @author Devon Koh <devon@silverstream.my>
 * @package  snap.interface
*/
interface IObserver
{
    /**
     * This method will be called by IObserverable object to notify of changes that has been done to it.
     *
     * @param  IObservable  $changed The initiating object
     * @param  IObservation $state   Change information
     * @return void
     */
    public function onObservableEventFired(IObservable $changed, IObservation $state);
}

/**
 * The observable interface is for managers or objects that needs to provide observation points
 * for objects and actions.
 * @author Devon Koh <devon@silverstream.my>
 * @package  snap.interface
 */
interface IObservable
{
    /**
     * This method will register the Observer object interested in our states
     * @param  IObserver $observer The observer interested to be notified of our status
     * @return void
     */
    public function attach(IObserver $observer);

    /**
     * This method will unregister any objects that are already registered with us
     * @param  IObserver $observer The observer interested to be deregistered
     * @return Boolean              True if successfully deregister. False otherwise.
     */
    public function detach(IObserver $observer);

    /**
     * This is the main logic call from the observable class to notify all interested observers
     * about the changes.
     *
     * @param  IObservation $state Observation status
     * @return void
     */
    public function notify(IObservation $state);
}

/**
 * Observation object is the object passed to the observer regarding to inform the observer about
 * specific changes that has been applied to the particular object.
 * @author Devon Koh <devon@silverstream.my>
 * @package  snap.interface
 */
class IObservation
{
    const ACTION_NONE				=  0;
    const ACTION_NEW 				=  1;
    const ACTION_EDIT 				=  2;
    const ACTION_DELETE 			=  3;
    const ACTION_FREEZE 			=  4;
    const ACTION_UNFREEZE   		=  5;
    const ACTION_APPROVE    		=  6;
    const ACTION_REJECT     		=  7;
    const ACTION_VERIFY     		=  8;
    const ACTION_ASSIGN     		=  9;
    const ACTION_CANCEL     		= 10;
    const ACTION_OTHER      		= 11;
    const ACTION_REVERSE			= 12;
    const ACTION_CONFIRM    		= 13;
    const ACTION_OPERATORAPPROVE	= 14;
    const ACTION_OPERATORREJECT		= 15;
    const ACTION_CHANGEREQUEST 		= 16;
    const ACTION_PRINT              = 17;


    public $target = null;
    public $action = 0;
    public $startState = 0;
    public $otherParams = array();

    public function __construct($target, $action, $initialStatus, $otherParams = array())
    {
        $this->target = $target;
        $this->action = $action;
        $this->startState = $initialStatus;
        if ($otherParams) {
            $this->otherParams = $otherParams;
        }
        return $this;
    }

    public function isNoAction()
    {
        return $this->action && self::ACTION_NONE;
    }
    public function isNewAction()
    {
        return self::ACTION_NEW == $this->action;
    }
    public function isEditAction()
    {
        return self::ACTION_EDIT == $this->action;
    }
    public function isDeleteAction()
    {
        return self::ACTION_DELETE == $this->action;
    }
    public function isFreezeAction()
    {
        return self::ACTION_FREEZE == $this->action;
    }
    public function isUnfreezeAction()
    {
        return self::ACTION_UNFREEZE == $this->action;
    }
    public function isApproveAction()
    {
        return self::ACTION_APPROVE == $this->action;
    }
    public function isRejectAction()
    {
        return self::ACTION_REJECT == $this->action;
    }
    public function isVerifyAction()
    {
        return self::ACTION_VERIFY == $this->action;
    }
    public function isAssignAction()
    {
        return self::ACTION_ASSIGN == $this->action;
    }
    public function isCancelAction()
    {
        return self::ACTION_CANCEL == $this->action;
    }
    public function isOtherAction()
    {
        return self::ACTION_OTHER == $this->action;
    }
    public function isReverseAction()
    {
        return self::ACTION_REVERSE == $this->action;
    }
    public function isConfirmAction()
    {
        return self::ACTION_CONFIRM == $this->action;
    }
    public function isOperatorapprovAction()
    {
        return self::ACTION_OPERATORAPPROV == $this->action;
    }
    public function isOperatorrejectAction()
    {
        return self::ACTION_OPERATORREJECT == $this->action;
    }
    public function isChangerequestAction()
    {
        return self::ACTION_CHANGEREQUEST == $this->action;
    }
}

/**
 * This trait is meant to be used and included directly into classes that implements the observable interface for
 * to reduce duplications in using this observer pattern
 * @author Devon Koh <devon@silverstream.my>
 * @package  snap.interface
 */
trait TObservable
{
    /**
     * Array to keep the observers that are interested in the state of things.
     * @var array
     */
    protected $observers = array();

    /**
     * This method will register the Observer object interested in our states
     * @param  IObserver $observer The observer interested to be notified of our status
     * @return void
     */
    public function attach(IObserver $observer)
    {
        foreach ($this->observers as $anObserver) {
            if ($anObserver == $observer) {
                if (method_exists($this, 'log')) {
                    $this->log("The attached observer has been registered already.", SNAP_LOG_WARNING);
                }
                return;
            }
        }
        $this->observers[] = $observer;
    }

    /**
     * This method will unregister any objects that are already registered with us
     * @param  IObserver $observer The observer interested to be deregistered
     * @return Boolean              True if successfully deregister. False otherwise.
     */
    public function detach(IObserver $observer)
    {
        foreach ($this->observers as $key => $object) {
            if ($object == $observer) {
                unset($this->observers[$key]);
                return true;
            }
        }
        return false;
    }

    /**
     * This is the main logic call from the observable class to notify all interested observers
     * about the changes.
     *
     * @param  IObservation $state Observation status
     * @return void
     */
    public function notify(IObservation $state)
    {
        foreach ($this->observers as $anObserver) {
            $anObserver->onObservableEventFired($this, $state);
        }
    }
}

/**
 * This trait includes code for providing general logging facilities across all class that needs it.
 * @author Devon Koh <devon@silverstream.my>
 * @package  snap.trait
 */
trait TLogging
{
    /**
    * Log object initialised here
    * @internal
    *
    * @var \Monolog
    */
    protected $logger = null;

    /**
     * Log messages to a logging facility
     *
     * @internal
     * @param string $msg message to log
     * @param int $priority priority level at which to log.
     * @param array $context context information to carry.
     *
     * @return void
     */
    public function log($msg, $priority = SNAP_LOG_INFO, $context = array())
    {
        if (property_exists(get_class($this), 'logger') && $this->logger != null) {
            $this->logger->log($priority, $msg, $context);
        } elseif (property_exists(get_class($this), 'app') && $this->app != null) {
            $this->logger = $this->app->getLogger();
            if ($this->logger) {
                $this->logger->log($priority, $msg, $context);
            }
        } elseif (! $this instanceof App) {
            $this->logger = App::getInstance()->getLogger();
            if ($this->logger) {
                $this->logger->log($priority, $msg, $context);
            }
        }
    }

    /**
    * Log messages to a logging facility
    *
    * @param string $msg message to log
    * @param int $priority priority level at which to log.
    *
    * @return void
    */
    public function logDebug($msg)
    {
        $this->log($msg, SNAP_LOG_DEBUG);
    }

    /**
    * Log messages to a logging facility
    *
    * @param string $msg message to log
    * @param int $priority priority level at which to log.
    *
    * @return void
    */
    public function logInfo($msg)
    {
        $this->log($msg, SNAP_LOG_INFO);
    }

    /**
    * Log messages to a logging facility
    *
    * @param string $msg message to log
    * @param int $priority priority level at which to log.
    *
    * @return void
    */
    public function logWarning($msg)
    {
        $this->log($msg, SNAP_LOG_WARNING);
    }


    /**
    * Log messages to a logging facility
    *
    * @param string $msg message to log
    * @param int $priority priority level at which to log.
    *
    * @return void
    */
    public function logCritical($msg)
    {
        $this->log($msg, SNAP_LOG_CRITICAL);
    }

    /**
    * Log messages to a logging facility
    *
    * @param string $msg message to log
    * @param int $priority priority level at which to log.
    *
    * @return void
    */
    public function logEmergency($msg)
    {
        $this->log($msg, SNAP_LOG_EMERGENCY);
    }

    /**
     * This method allows user to set a specifc channel or the log messages
     */
    public function setLogChannel($channelName)
    {
        $this->logger = App::getInstance()->getLogger()->withName($channelName);
    }
}

//////////////////////////////////////////  Event Management Interfaces  //////////////////////////////////////////
/**
 * Wrap the event processordata class
 * @author Devon Koh <devon@silverstream.my>
 * @package  snap.interface
 */
interface IEventProcessorData
{
    public function getSubject();
    public function getLogContent();
    public function getObjectId();
    public function getReference();
    public function getEventGroupTypeId();
    public function getEventGroupId();
    public function getEventModuleId();
    public function getEventObjectId();
    public function getReceiver();
    /**
     * This method will implement serializing the object into a cacheable string for optimum storage.
     * @return String
     */
    public function toCache();

    /**
     * This method will need to implement expanding the object back to its original from the cached data provided.
     * @param  string $data The original data provided in toCache()
     * @return void
     */
    public function fromCache($data);
}

/**
 * Interface to define the processor that can be used to act on an event
  * @author Devon Koh <devon@silverstream.my>
 * @package  snap.interface
*/
interface IEventProcessor
{
    public function registerEventForProcessing($app, IEventTrigger $trigger, IObservable $generator, IObservation $target) /*: IEventProcessorData*/;

    /**
     * This method is used to actually do processing for the event that has been triggered.   Implementation will be
     * specific to the processor that is implementing it.
     *
     * @param  App              $app            Application object
     * @param  IEventProcessorData $registeredData The data that will be used for performing the process.
     * @return void                              none
     */
    public function processEvent($app, IEventProcessorData $registeredData);

    /**
     * This method is used to validate the receiver's format
     * If email process, check if receiver is in email format
     * If telegram process, check if receiver is in telegram recipient's format
     *
     * @param  App              $app            Application object
     * @param  IEventProcessorData $registeredData The data that will be used for performing the process.
     * @return Boolean
     */
    public function validateReceiver($receiver);
}

/**
 * Interface to define a trigger that will match event provided
 * @author Devon Koh <devon@silverstream.my>
 * @package  snap.interface
 */
interface IEventTrigger
{
    /**
     * This method will generate an eventlog object formatted as required
     * @param  Snap\App       $app     The application class
     * @param  IObservable  $generator The source of event being generated.  Usually a manager.
     * @param  IObservation $target    The event information as a Observation object
     * @return Snap\object\eventlog    The eventlog object containing the messages
     */
    public function generateEventLog($app, IObservable $generator, IObservation $target);

    /**
     * This method will return a boolean value indicating if the trigger matches the current event being notified.
     * @param  Snap\App       $app     The application class
     * @param  IObservable  $generator The source of event being generated.  Usually a manager.
     * @param  IObservation $target    The event information as a Observation object
     * @return boolean                 True if this trigger is activated by the event coming in.
     */
    public function matchesEvent($app, IObservable $generator, IObservation $target);
}

/**
 * This interface is used to provide customisation for how an event can be triggered by the system.  There is a default implementation
 * as provided named 'defaulteventtriggermatcher'
 * @author Devon Koh <devon@silverstream.my>
 * @package  snap.interface
 */
interface IEventTriggerMatcher
{
    /**
     * This method will return a boolean value indicating if the trigger matches the current event being notified.
     * @param  Snap\App       $app     The application class
     * @param  IObservable  $generator The source of event being generated.  Usually a manager.
     * @param  IObservation $target    The event information as a Observation object
     * @return boolean                 True if this trigger is activated by the event coming in.
     */
    public function matchesEvent($app, IEventTrigger $trigger, IObservable $generator, IObservation $target);
}

/**
 * This interface represents a configuration setting for the event module to identify key application
 * settings that will need to be supported by the event management module itself.
 * @author Devon Koh <devon@silverstream.my>
 * @package  snap.interface
 */
interface IEventConfig
{
    /**
     * @return Array[]   Expects to return an array of ['id', 'name', 'desc'] values representing the actions that is to be taken.
     *	                 The data in this map is primarily used for displaying to the user what actions is a particular event
     *                   associated to.
     */
    public function getEventActionMap($app);

    /**
     * @return Array[]   Expects to return an array of ['id', 'name', 'desc'] values representing the modules that is to be taken.
     *	                 The data in this map is primarily used for displaying to the user what actions is a particular event
     *                   associated to.
     */
    public function getEventModuleMap($app);

    /**
     * @return Array[]   Expects to return an array of ['id', 'name'] values representing the group type that are available
     *                   in the system.  The group type is a category used to differentiate users within the system.  E.g.  partnertype / branch
     */
    public function getEventGroupTypeMap($app);

    /**
     * @return Array[]   Returns array of the ['id', 'object'] representing the event procesor available to process event.  Triggers that are available
     *                    without its event processor can not be successfully processed.
     */
    public function getEventProcessor($app);

    /**
     * Returns the category of user
     * @return int  The ID of the group type (in the above map) that the current session user belongs to
     */
    public function getSessionGroupTypeId($app);

    /**
     * Returns the group Id that this current user session belongs to.
     * @return int  The ID of the group that the current session user belongs to
     */
    public function getSessionGroupId($app);
}

/**
 * This interface is used to define methods that can be accessed for access to caching within the system.
 * Actual implementation currently supports memcache and redis.
 */
interface ICacher {
    /**
     * Gets a stored data by the key
     * @param  String $key The key that the data is stored in
     * @return String      Value
     */
    public function get($key);

    /**
     * Sets data by given the key
     * @param  String $key      The key that the data is stored in
     * @param  String $variable The data to store
     * @param  String $ttl      How long should the data be kept
     * @return boolean  True if successful.  False otherwise.
     */
    public function set($key, $variable, $ttl = 0);

    /**
     * Removes the key from storage
     * @param  String $key   The key identifying data to be purged.
     * @return boolean  True if successful.  False otherwise.
     */
    public function delete($key);

    /**
     * Pessimistic locking and reserving for a key.

     * @param  String $key          The key that the data is stored in
     * @param  String $value        The data to store
     * @param  Number $ttl          How long should the data be kept
     * @param  Number $timeout      How long to wait for the reservation before timing out.
     * @return boolean.  True if successful reserved the key and set the value.  False otherwise.
     */
    public function waitForLock($key, $value, $ttl, $timeout = -1);

    /**
     * Unlocks the key for use by other processes.  Pairs this with waitForLock()
     * @param  String $key          The key that the data is stored in
     */
    public function unlock($key);

    /**
     * Atomically sets adds a key
     * @param  String $key          The key that the data is stored in
     * @param  String $value        The data to store
     * @param  Number $ttl          How long should the data be kept
     */
    public function add($key, $value, $ttl);

    /**
     * Atomically increment a value that is stored
     * @param  String $key          The key that the data is stored in
     * @param  Number $amount       Increment or decrement by the amount set.
     *                               positive number means increment.  negative numbers means decrement.
     * @param  Number $ttl          How long should the data be kept
     */
    public function increment($key, $amount = 1, $ttl = 0);

    /**
     * Returns the actual instance of the underlying engine to implement engine specific actions.
     * @return Cache engine object.
     */
    public function getEngine();

    /**
     * Provides a quickway to run multiple commands at the same time.
     * @param  function $callbackFunction   The function should take one parameter that is the actual engine object.
     * @return mixed.                       Return data form the commands ran.
     */
    public function runBulkCommands(Array $callbackFunction, $cacheName = null);
}

interface IConditionFilter {
    /**
     * Prepares conditions for query based on user or grid input
     * @param  App          $app      The application instance
     * @param  array        $params   All request parameters from browser.
     * @param  SnapObject   $object   The snap object that query will get out.
     * @param  array        $filters  Condition array from grid.
     * @param  SqlRecorder  $recorder Used to record the SQL for use.
     */
    public function getNameValues($app, $params, $object, $filters, $recorder);
}

interface IMerchantSettlementAPI
{
    /**
     * This method will be called in the event that the XPay system receives a redirection from the merchant site
     * requesting a fund in process.  This function will process the requests provided and checks if they are of
     * valid format and if the request can proceed.  The function will also prepare and decrypt all the parameters
     * passed by the merchant and returns the data in the array specified below.
     *
     * @return Array.  The array will be an associative array with the following keys:
     *                      -validRequest - boolean.    Whether the request can proceed or not.
     *                      -errorCode      integer     IF the request is not valid, an error code should be provided.
     *                      -errorDesc      string      The description of the error in more understandable language term
     *                      -merchantID     string      Merchant ID for this transaction;
     *                      -refID          string      Merchant provided reference ID.
     *                      -memberID       string      The member user name that is making this fund in transaction.
     *                      -currency       char(3)     The currency code for this transaction.
     *                      -amount         integer     The amount to fund out in cents
     *                      -accountName    string      The account holder name to bank into.
     *                      -accountNo      String      The account number for this transaction
     *                      -province       String      The destination bank's province
     *                      -branch         string      The destination bank's branch address
     *                      -city           string      The destination bank's city
     *                      -bankCode       string      Specified bank to do the transaction
     *                      -transTime      datetime    Merchant's provided transaction time.
     *                      -remarks        string      Remarks from the merchant
     *                      -notifyURL      String      The URL to post the transaction results to the server backend.
     *
     */
    function decodeNewSettlementRequest();

    /**
     * This method is used to format a response for the merchant side as a result of their request.
     *
     * @param  partner   $partner Partner to generate for
     * @param  settlementApi $settlementAPI The settlementAPI object to send data about
     * @return String             The XML formatted result to be returned.
     */
    function formatResponse(partner $partner, settlementApi $settlementAPI);

    /**
     * This method is used to format an error type of response to the merchant side as a result of their request.
     * @param  partner   $partner   Partner to generate for
     * @param  string    $errorCode Error code for the merchant to identify the error
     * @param  string    $errorDesc Description of the error for easier understanding.
     * @return string               XML formatted data to be returned to the merchant
     */
    function formatErrorResponse(partner $partner, $errorCode, $errorDesc, $miscData);

    /**
     *  This method is used to notify the merchant system of the results for the transaction.  If the results is in error, additional
     *  information such as an error code and error description can be posted as well.
     *
     * @param  settlementApi $settlementAPI   The settlementAPI object to notify on
     * @param  string    $errorCode [optional]  Error code indicating the specific error that occured
     * @param  string    $errorDesc [optional]  Error description string
     * @return Array.  The array will be an associative array with the following keys:
     *                      -httpcode       string      The HTTP response code returned.
     *                      -url            string      The notified URL.
     *                      -totaltime      string      Total time used for the connection
     *                      -connecttime    string      Connection time;
     *                      -notified       boolean     Whether we have successfully notified the merchant
     *                      -verified       boolean     True if the merchant has verified that they received request.
     *                      -errmsg         string      Additional error message to be logged if needed
     *                      -content        string      Full response content from the merchant.
     *                      -senddata       string      The actual data that has been send over to the server
     */
    function notifyMerchantSettlementResult(settlementApi $settlementAPI, $errorCode = '', $errorDesc = '');
}

/**
 * This interface defines proce provider api features
 */
interface IGtpPriceCollectorAPI
{
    /**
     * Initialise the api with its specific configuration
     * @param  \Snap\App                  $app           App object
     * @param  \Snap\object\PriceProvider $priceProvider Configuration to use for this api
     */
    function initialise($app, \Snap\object\PriceProvider $priceProvider);

    /**
     */
    /**
     * This method will start the price collection engine.
     */
    function run();

    /**
     * This method will stop the price collection process
     */
    function stop();

    /**
     * Determines if the price collection engine is currently operating or not.
     * @return boolean True if is running.  False otherwise.
     */
    function isRunning();
}

/**
 * Interface to define how a ApiProcessor should behave
 */
interface IApiProcessor
{

    /**
     * Main method to process the incoming request.  Implemented class can get relevant
     * information about the action to be taken etc from the apiParam and then call the
     * appropriate manager to execute the main business logics.
     * 
     * @param  App                      $app           App Class
     * @param  \Snap\api\param\ApiParam $apiParam      Api parameter object containing the decoded data
     * @param  array                    $decodedData   Decoded and converted data
     * @param  array                    $requestParams Original raw data from the API request
     * @return \Snap\api\param\ApiParam     The response type represented as apiParams.
     */
    function process($app, \Snap\api\param\ApiParam $apiParam, $decodedData, $requestParams);
}

interface IApiSender
{
    /**
     * Main method to response to client
     * 
     * @param  App                      $app           App Class
     * @param  mixed  $responseData Data to send
     * @param  mixed $destination   Depending on sender property, this could be an URL to connect and send data
     */
    function response($app, $responseData, $destination = null);
}

/**
 * This interface provides methods for a strategy to determine matching mechanism to be used
 * and to indicate the matching prices
 */
interface IFutureOrderMatchStrategy
{
    /**
     * This method is designed to be called on obtaining a new price data.  The method should return 
     * true if a matching should be done on the price stream data.  Otherwise return false.
     * 
     * @param  \Snap\Application $app
     * @param  \Snap\object\PriceProvider $provider   The price provider
     * @param  \Snap\object\PriceStream $priceStream New price data received
     * @return boolean.  True if matching should proceed.  False otherwise.
     */
    function canMatchNewPrice($app, \Snap\object\PriceProvider $provider, \Snap\object\PriceStream $priceStream);

    /**
     * This method will be invoked on a scheduled interface and the strategy implementation will
     * determine if matching should proceed at this particular trigger interval.
     * @param  \Snap\Application $app
     * @param  \Snap\object\PriceProvider $provider   The price provider
     * @return boolean.  True if matching should proceed.  False otherwise
     */
    function canMatchOnTrigger($app, \Snap\object\PriceProvider $provider);

    /**
     * This method is called when a new future order is received from the system so that the strategy can decide if
     * matching should be reset or not.
     * 
     * @param  \Snap\Application $app
     * @param  \Snap\object\OrderQueue $futureOrder  The future order object
     */
    function onNewFutureOrderReceived($app, \Snap\object\OrderQueue $futureOrder);

    /**
     * Returns the configuration that we need to perform the matching with.  This
     * method will only be called when canMatchNewPrice() or canMatchOnTrigger() returns true.
     * @return array with values ($toMatchCompanyBuy, $toMatchCompanySell, $priceStreamObject)
     */
    function getMatchFutureOrderConfig();
}


/**
 * Interface for a localizable object
 */
interface ILocalizable
{
    /**
     * Main method to decode a localized content to be used in the localizable object
     * 
     * @param \Snap\object\MyLocalizedContent  $content   The localized content object which contains localized data
     */
    function decodeContent(\Snap\object\MyLocalizedContent $content);


    /**
     * Method to encode the localizable object's content into a string to be stored in a LocalizedContent
     * 
     * @return string   Encoded content 
     */
    function encodeContent();
}

interface IEKycProvider
{

    /**
     * Main method to create a submission with necessary facial data to the e-KYC provider
     * 
     * @param \Snap\App                    $app          Snap Application
     * @param \Snap\object\MyAccountHolder $accHolder    The account holder
     * @param array                        $params       The required data to be submitted
     * 
     * @return \Snap\object\MyKYCSubmission  Returns the EKYC submission record
     */
    function createSubmission($app, $accountHolder, $params);

    /**
     * Method to submit the submission to the e-KYC Provider.
     * 
     * @param \Snap\App                    $app         Snap Application
     * @param \Snap\object\MyKYCSubmission $submission  The submission object
     * 
     * @return \Snap\object\MyKYCSubmission     The updated submission
     */
    function submitSubmission($app, $submission);

    /**
     * Method to get the results for a submission that was sent from submitData()
     * 
     * @return \Snap\object\MyKYCResult the result
     */
    function getResult($app, $submission);

    /**
     * Method to know if a submission is ready for the provider  
     * 
     * @param \Snap\object\MyKYCSubmission $submission  The submission object
     * 
     * @return boolean
     */
    function canSubmitToProvider($submission);
}

interface IPepProvider
{
    /**
     * Method to search for pep records 
     * @param  \Snap\App                      $app            Snap Application
     * @param  \Snap\object\MyAccountHolder   $accountHolder  The account holder
     * @param  array                          $params         The search parameters
     * @return \Snap\object\MyPepSearchResult the result
     */
    function searchForPepRecords($app, $accountHolder, $params);
    
    /**
     * Method to search for pep records 
     * 
     * @param  int $id  The id of the pep record
     * 
     * @return string
     */
    function getPepJSON($id);

    /**
     * Method to search for pep records 
     * 
     * @param  int $id  The id of the pep record
     * 
     * @return string
     */
    function getPepPDF($id);
}
interface IFpxOperation
{
    /**
     * Function to create/initialize a transaction on FPX provider side
     * 
     * @param \Snap\object\MyAccountHolder $accountHolder
     * @param \Snap\object\MyPaymentDetail $paymentDetail
     * 
     * @return mixed|bool   Return false if not success, else return some data
     */
    function initializeTransaction($accountHolder, $paymentDetail);

    /**
     * Retrieve status of payment from FPX provider side
     */
    function getPaymentStatus($paymentDetail);

    /**
     * Verifies payment result
     * 
     * @return boolean
     */
    function verifyPaymentResponse($response);
}

interface IPayoutProviderOperation
{
    /**
     * 
     * @param \Snap\object\MyAccountHolder $accountHolder 
     * @param \Snap\object\MyDisbursement  $disbursement 
     * @return bool
     */
    function createPayout($accountHolder, $disbursement);

    function getPayoutStatus($disbursement);
}

interface ICasaOperation
{
    /**
     * Function to create/initialize a transaction on CASA provider side
     * 
     * @param \Snap\object\MyAccountHolder $accountHolder
     * @param \Snap\object\MyPaymentDetail $paymentDetail
     * 
     * @return mixed|bool   Return false if not success, else return some data
     */
    function initializeTransaction($accountHolder, $paymentDetail);
    
    /**
     * 
     * @param \Snap\object\MyAccountHolder $accountHolder 
     * @param \Snap\object\MyDisbursement  $disbursement 
     * @return bool
     */
    function createPayout($accountHolder, $disbursement);

    /**
     * Retrieve customer info from CASA provider side
     */
    function getCustomerInfo($input);

    /**
     * Retrieve casa info from CASA provider side
     */
    function getCasaInfo($input);
}

/**
 * Interface IStorageFeeChargeStrategy
 * 
 * This interface defines a strategy for calculating the chargeable weight in grams
 * based on various inputs such as app, account holder, partner, and charge date.
 */
interface IStorageFeeChargeStrategy {
    /**
     * Calculate the chargeable weight in grams.
     * 
     * @param mixed $app          The application context or configuration.
     * @param mixed $accountHolder The account holder for whom the fee is calculated.
     * @param mixed $partner      The partner entity involved in the transaction.
     * @param mixed $chargedon    The date or time when the charge is applied.
     * 
     * @return int The calculated chargeable weight in grams.
     */
    public function calculateChargeableGram($app, $accountHolder, $partner, $chargedon);
}

?>