<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap;

Use Snap\store\dbdatastore as DbDatastore;
Use Snap\store\userlogstore as userlogstore;
Use Snap\store\eventsubscriberstore as eventSubscriberStore;
Use Snap\usersession;
Use Snap\InputException;

define('SNAP_RIGHT_VIEW', 1);
define('SNAP_RIGHT_ADD', 2);
define('SNAP_RIGHT_EDIT', 3);
define('SNAP_RIGHT_DELETE', 4);
define('SNAP_RIGHT_FREEZE', 5);
define('SNAP_RIGHT_UNFREEZE', 6);

/**
 * This controller class implements the basic framework entities binding and facilities.  Inherit from this
 * class to extends its functionality and then add a key in the config file setting to indicate the new
 * controller class to use.
 *
 * @method  Snap\manager xxxManager ([xxx]) Generic method to get registered manager from the controller.  The xxx is the key name of the registered manager.
 * @method  Snap\datastore userlogStore ([userlog]) Generic method to get published datastore from the controller.  The userlog is the key name of the registered data store.
 * @method  Snap\datastore userStore ([user]) Generic method to get published datastore from the controller.  The xxx is the key name of the registered data store.
 * @method  Snap\datastore appstateStore ([appstate]) Generic method to get published datastore from the controller.  The xxx is the key name of the registered data store.
 * @method  Snap\datastore iprestrictionStore ([iprestriction]) Generic method to get published datastore from the controller.  The iprestriction is the key name of the registered data store.
 * @method  Snap\datastore eventjobStore ([eventjob]) Generic method to get published datastore from the controller.  The eventjob is the key name of the registered data store.
 * @method  Snap\datastore eventlogStore ([eventlog]) Generic method to get published datastore from the controller.  The eventlog is the key name of the registered data store.
 * @method  Snap\datastore eventmessageStore ([eventmessage]) Generic method to get published datastore from the controller.  The eventmessage is the key name of the registered data store.
 * @method  Snap\datastore eventsubscriberStore ([eventsubscriber]) Generic method to get published datastore from the controller.  The eventsubscriber is the key name of the registered data store.
 * @method  Snap\datastore eventtriggerStore ([eventtrigger]) Generic method to get published datastore from the controller.  The eventtrigger is the key name of the registered data store.
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.base
 */
class Controller implements IAppController
{
    Use TLogging;

    protected $app = null;

    protected $datastores = array();
    protected $fulldatastores = array();
    protected $datastoreChildRegistry = array();

    /**
     * Stores managers that will be used by the application.
     *
     * @var array of manager classes and its settings
     */
    protected $managers = array();

    /**
     * Used to store the manager key od (observable manager) -> (observer  manager list)
     * @var array
     */
    protected $observerableArray = array();

    /**
     * This method will be called everytime the appplication framework starts up to allow proper initialisation of the
     * controller.  The initialisation can further take in the application context or mode as defined.
     *
     * @param  int $contextOrMode  The currently running mode of the application
     * @return void
     */
    public function initialiseController($app, $contextOrMode)
    {
        $this->app = $app;
        
        $stores = [
            'userlog' => ['class' => '\\Snap\\store\\userlogstore', 'publicAccessible' => true,
                'parameters' => [$app->getDBHandle(), 'userlog', '', 'usl', "\\Snap\\object\\UserLog",
                                 array(), array(), true]
            ],
            'appstate' => ['class' => '\\Snap\\store\\dbdatastore', 'publicAccessible' => true,
                'parameters' => [$app->getDBHandle(), 'app_state', '', 'stt', "\\Snap\\object\\AppState",
                                 array(), array(), true]
            ],
            'user' => ['class' => '\\Snap\\store\\dbdatastore', 'publicAccessible' => true,
                        'parameters' => [$app->getDBHandle(), 'user', '', 'usr', "\\Snap\\object\\User",
                                         array('vw_user','vw_userrolelink'), array(), true]
            ],
            'iprestriction' => ['class' => '\\Snap\\store\\iprestrictionstore', 'publicAccessible' => true,
                        'parameters' => [$app->getDBHandle(), 'iprestriction', '', 'ipr', "\\Snap\\object\\IPRestriction",
                                         array('vw_iprestriction'), array(), true]
            ],
            'eventjob' => ['class' => '\\Snap\\store\\dbdatastore', 'publicAccessible' => true,
                        'parameters' => [$app->getDBHandle(), 'eventjob', '', 'evj', "\\Snap\\object\\EventJob", array(), array(), true]
            ],
            'eventlog' => ['class' => '\\Snap\\store\\dbdatastore', 'publicAccessible' => true,
                        'parameters' => [$app->getDBHandle(), 'eventlog', '', 'evt', "\\Snap\\object\\EventLog",
                                         array('vw_eventlog'), array(), true]
            ],
            'eventmessage' => ['class' => '\\Snap\\store\\dbdatastore', 'publicAccessible' => true,
                        'parameters' => [$app->getDBHandle(), 'eventmessage', '', 'evm', "\\Snap\\object\\EventMessage",
                                         array(), array(), true]
            ],
            'eventsubscriber' => ['class' => '\\Snap\\store\\eventSubscriberStore', 'publicAccessible' => true,
                        'parameters' => [$app->getDBHandle(), 'eventsubscriber', '', 'evs', "\\Snap\\object\\EventSubscriber",
                                         array(), array(), true]
            ],
            'eventtrigger' => ['class' => '\\Snap\\store\\dbdatastore', 'publicAccessible' => true,
                        'parameters' => [$app->getDBHandle(), 'eventtrigger', '', 'etr', "\\Snap\\object\\EventTrigger",
                                        array('vw_eventtrigger'), array('subscriber' => $this->datastores['eventsubscriber']), true]
            ],
            'user' => ['class' => '\\Snap\\store\\dbdatastore', 'publicAccessible' => true,
                        'parameters' => []
            ],
        ];

        // Updated by Cheok on 2020-11-06 to allow creation of test tables during unit tests

        // //Added by Devon on 2018/10/15 to reigster the stores to the fulldatastores as well.
        // $this->fulldatastores = $this->datastores;
        // //End add 2018/10/15

        foreach ($stores as $storeKey => $storeProps) {
            $this->registerStore($storeKey, $storeProps, is_array($storeProps) ? $storeProps['publicAccessible'] : true);
        }

        // End update by Cheok
        return true;
    }

    /**
     * This method will be called at the end in preparation of ending the application processes
     * @param  int $contextOrMode  The currently running mode of the application
     * @return void
     */
    public function shutdownController($contextOrMode)
    {
    }

    /**
     * Returns the entity store object based on the key array.
     * @param  string $storeKey The name to associate this store with.  The name will be used to access the store later.
     *                          @example  if key is 'user' then access the store with $app->userStore() method.
     * @param  IEntityStore $parentStore   The parent store that we are getting the child store for.  Privately available
     *                                       datastores will need to provide its parent store.
     * @return IEntityStore|null           The store object if available.  null otherwise
     */
    public function getStore($storeKey, $parentStore = null)
    {
        //Added by Devon on 2018/8/24 to support lazy loading of child datastores as well.
        if ($parentStore) {
            if (method_exists($parentStore, 'getTableName')) {
                $tableName = $parentStore->getTableName();
            } else {
                $tableName = 'general';
            }
            if (isset($this->fulldatastores[$storeKey]) && in_array($storeKey, $this->datastoreChildRegistry[$tableName], true)) {
                return $this->startupStore($storeKey);
            }
        }
        //End Add by Devon on 2018/8/24
        if (is_object($this->datastores[$storeKey])) {
            return $this->datastores[$storeKey];
        } elseif (is_array($this->datastores[$storeKey])) {
            return $this->startupStore($storeKey);
        }
        $this->log(__CLASS__." Unable to find the datastore instance for the key provided: $storekey", SNAP_LOG_ERROR);
        //throw new \Exception(__CLASS__." Unable to find the datastore instance for the key provided: $storekey");
        return null;
    }

    /**
     * This method will register a entity store object with the application so that it can be easily obtained throughout the
     * application.
     *
     * @param  String       $storeKey    The key name associated with the store
     * @param  mixed        $initialData The IEntityStore  or an array with the keys ('class', 'parameters') in uninitialised format.
     * @return void
     */
    protected function registerStore($storeKey, $initialData, $publicAccessible = true)
    {
        if (! ((is_array($initialData) && isset($initialData['class']) && isset($initialData['parameters'])) ||
           $initialData instanceof IEntityStore)) {
            throw new InputException("Controller::registerStore() takes IEntityStore object or array containing class & parameters key only", InputException::GENERAL_ERROR);
        }
        $this->fulldatastores[$storeKey] = $initialData;
        if ($publicAccessible) {
            $this->datastores[$storeKey] = $initialData;
        }
    }

    public function getManager($managerKey)
    {
        $managerKey = strtolower($managerKey);
        if (isset($this->managers[$managerKey])/*array_key_exists($managerKey, $this->managers)*/) {
            if (! is_array($this->managers[$managerKey])) {
                return $this->managers[$managerKey];
            } else {
                return $this->startupManager($managerKey);
            }
        }
        return null;
    }

    protected function registerManager($managerKey, $managerClass, $observableTargets = array())
    {
        $this->managers[strtolower($managerKey)] = array('classname' => $managerClass, 'observableTargets' => $observableTargets);
        foreach ($observableTargets as $aManagerKey) {
            if (! preg_match('/(store|factory)$/i', $aManagerKey)) {
                $this->observerableArray[$aManagerKey][] = $managerKey;
            } else {
                //We have to start up this class immediately because we have to link it to the data store
                $store = call_user_func_array(array($this, $aManagerKey), null);
                if ($store && $store instanceof \Snap\IObservable) {
                    $theManager = $this->getManager($managerkey);
                    $store->attach($theManager);
                }
            }
        }
    }

    /**
     * This is the factory method to create a usersession object for the application to use.
     * @param  App    $app The application instance requesting for the usersession.
     * @return usersession User session object to be returned
     */
    public function createUsersession(App $app)
    {
        return new \Snap\usersession($app);
    }

    /**
     * This method can be used to customise the built in request router by adding parameters to it
     *
     * @param  RequestRouter $router            The request router object to customise on
     * @param  Int           $contextOrMode     The application run mode or context
     * @return void
     */
    public function initialiseRequestRouter(RequestRouter $router, $contextOrMode)
    {
        //You can customise the default requestRouter here, e.g.
        //$router->mapHandler( 'somehandler', 'Snap\handler\some-non-standard-handler' );
    }

    /**
     * This method should be implemented and return an array with the key representing the permission
     * key (can be multiple level by specifying a path E.g.  /root/system/user/add, /root/system/user/edit).
     * The value of the array shall be a description of the permission.
     *
     * This method can be used to initialise the rbac tables with the initial permissions.  It will also
     * be used by the user-role handler to allow for configuration of the application permission.
     */
    public function getAvailableApplicationPermission()
    {
        return [
            '/root' => 'Top Level',
            '/root/system' => gettext('System management module'),
            '/root/system/user' => gettext('User management module'),
            '/root/system/user/list' => gettext('View user'),
            '/root/system/user/add' => gettext('Add user'),
            '/root/system/user/edit' => gettext('Edit user'),
            '/root/system/user/resetpassword' => gettext('Reset password for all'),
            '/root/system/user/suspend' => gettext('Suspend user'),
            '/root/system/role' => gettext('Role management module'),
            '/root/system/role/list' => gettext('View role'),
            '/root/system/role/add' => gettext('Add role'),
            '/root/system/role/edit' => gettext('Edit role'),
            '/root/system/role/delete' => gettext('Delete role'),
            '/root/system/ip' => gettext('IP restriction module'),
            '/root/system/ip/list' => gettext('View IP restriction'),
            '/root/system/ip/add' => gettext('Add IP restriction'),
            '/root/system/ip/edit' => gettext('Update IP restriction'),
            '/root/system/ip/delete' => gettext('Delete IP restriction'),
            '/root/system/event/event_log' => gettext('Event logs'),
            '/root/system/event/event_subscription' => gettext('Event Subscription'),
            '/root/system/event/event_message' => gettext('Event Message'),
            '/root/developer/event' => gettext('Event trigger')
        ];
    }

    /**
     * This method will initialise all the rights to be used in the application.  The actual data will be obtained from
     * the getPermissionControlledModules() method.  The resulting data should be cached with the app controller version
     * info or something so that changes can be easily detected and updated.
     *
     * @param  String $appVersion   The application version info
     * @param  int    $userType     The user type that we are getting the rights for.
     * @return Array                An array of the permission according to the user type selected.
     */
    public function initialiseRights($appVersion, $userType = null)
    {
        $permissionData = App::getInstance()->getcache('iController_rights'.$userType);
        $permissionDesc = App::getInstance()->getcache('iController_rightsText'.$userType);
        if (! $permissionData || $permissionData['appversion'] != $appVersion) {
            $permissionData = array('appversion' => $appVersion);
            $modules = $this->getPermissionType();
            foreach ($modules as $aModule) {
                $obj = new $aModule;
                if (! $obj instanceof IHandler) {
                    throw new Exception("The class ($aModule) needs to implement IHandler interface to be used in Controller::initialiseRights()");
                }
                $modulePermission = $obj->getRights($userType);
                foreach ($modulePermission as $handlerName => $permissions) {
                    foreach ($permissions as $permissionItem) {
                        if ($permissionItem['description']) {
                            $permissionData[$handlerName."/".$permissionItem['object']] = $permissionItem['rights'];
                            $permissionDesc[$handlerName."/".$permissionItem['object']] = $permissionItem['description'];
                        }
                    }
                }
            }
            App::getInstance()->setCache('iController_rights'.$userType, $permissionData);
            App::getInstance()->setCache('iController_rightsText'.$userType, $permissionDesc);
        }
        return array($permissionData, $permissionDesc);
    }

    /**
     * Define here is we would like to delegate all the functions in this controller to the application object so that
     * the user can use it such as App::getInstance()->getXXXXXFromController() where getXXXXXFromController is the function
     * name defined in the class that implements this interface.
     *
     * @return boolean True if the functions here should be accessible from the app.  otherwise false.
     */
    public function isCallableFromApp()
    {
        return true;
    }

    public function __call($name, $arguments)
    {
        if (preg_match('/store$/i', $name)) {
            $dsname = strtolower(substr($name, 0, strlen($name) - 5));
            if (isset($this->datastores[$dsname])) {
                return $this->getStore($dsname);
            }
        } elseif (preg_match('/factory$/i', $name)) {
            $dsname = strtolower(substr($name, 0, strlen($name) - 7));
            if (isset($this->datastores[$dsname])) {
                return $this->getStore($dsname);
            }
        } elseif (preg_match('/manager$/i', $name)) {
            $managerName = strtolower(substr($name, 0, strlen($name) - 7));
            return $this->getManager($managerName);
        }
        throw new \Exception(__CLASS__.':'.__METHOD__." Unable to find a function with the name $name");
    }

    /**
    *  This method will initialise an instance of the manager that will be used by the system.
    *  There will only be one instance of manager for the system
    *
    *  @params $name String.  The module name to initialise.  See $this->managers array key.
    *
    *  @return The manager class created.
    */
    private function startupManager($name)
    {
        if (! array_key_exists($name, $this->managers)) {
            throw(new Exception(__CLASS__."::startupManager Encountered invalid manager reference $name"));
        }
        if (! is_array($this->managers[$name])) {
            return $this->managers[$name];
        }
        $managerSetting = $this->managers[$name];
        //1.  Create the new manager
        $this->managers[$name] = $theManager = new $managerSetting['classname'](App::getInstance());

        //2.  Go through each observable manager key and see if our key is in it.  If it is, then we need to create the
        //    corresponding manager and attach to them.
        if (isset($this->observerableArray[$name]) && $theManager instanceof \Snap\IObservable) {
            foreach ($this->observerableArray[$name] as $observerManagerKey) {
                $observerManager = $this->getManager($observerManagerKey);
                if ($observerManager instanceof \Snap\IObserver) {
                    $theManager->attach($observerManager);
                } else {
                    $this->log(__CLASS__.":startupManager() The manager $observerManagerKey is not an observable instance while attempting to initialise manager $name", SNAP_LOG_CRITICAL);
                    throw new \Exception(__CLASS__.":startupManager() The manager $observerManagerKey is not an observable instance while attempting to initialise manager $name");
                }
            }
        }
        return $this->managers[$name];
    }

    /**
     * This method will initialise a datastore in the form array so that it can be used by the system.
     * The method will also initialise all stores that are required by this store.
     *
     * @param  String $storeKey The datastore required
     * @param  Array  $level    The parent's key to avoid circular referencing.
     * @return datastore object created.
     */
    protected function startupStore($storeKey, $level = array())
    {
        $relatedStores = [];
        $config = $this->fulldatastores[$storeKey];
        if (is_object($config)) {
            return $config;
        }  //already object

        //We need to create and initialise the db object.
        $numArguments = count($config['parameters']);
        //We will loop through the parameters argument and try to figure out the related store location.
        //Always assume it is the array from the back and there is a key for it.
        $bFound = false;
        for ($i = $numArguments-1; 0 <= $i;  $i--) {
            if (is_array($config['parameters'][$i])) {
                foreach ($config['parameters'][$i] as $key => $storeName) {
                    //Added by Devon on 2018/8/24 to support for lazy loading of stores.
                    if (preg_match('/(.*)(lazystore|lazyfactory)$/i', trim($storeName), $matches) && is_string($key)) {
                        if (isset($this->fulldatastores[$matches[1]]) && ($this->fulldatastores[$matches[1]] instanceof IEntityStore) && ! in_array($storeKey, $level)) {
                            $config['parameters'][$i][$key] = $this->fulldatastores[$matches[1]];
                        } else {
                            $relatedStores[] = $matches[1];
                        }
                        $bFound = true;
                    } elseif (preg_match('/(.*)(store|factory)$/i', trim($storeName), $matches) && is_string($key)) {
                        if (! in_array($storeKey, $level)) { //maximum initialise 3 levels of datastore.
                            $level[] = $storeKey;
                            $store = call_user_func_array([$this, 'startupStore'], [$matches[1], $level]);  //initialise the store as well
                            $config['parameters'][$i][$key] = $store;
                        }
                        $bFound = true;
                    }
                    //End Add by Devon on 2018/8/24
                }
                if ($bFound) {
                    break;
                }
            }
        }
        //Creating the actual store and registering it.
        $r = new \ReflectionClass($config['class']);
        $store = $r->newInstanceArgs($config['parameters']);
        $this->fulldatastores[$storeKey] = $store;
        //Only store in publicly accessible store array if it already exists there.
        if (isset($this->datastores[$storeKey])) {
            $this->datastores[$storeKey] = $store;
        }
        //Added by Devon on 2018/8/24 to support for lazy loading of stores.
        if (count($relatedStores)) {
            if (method_exists($store, 'getTableName')) {
                $tableName = $store->getTableName();
            } else {
                $tableName = 'general';
            }
            $this->datastoreChildRegistry[$tableName] = $relatedStores;
        }
        //End Add by Devon on 2018/8/24
        return $store;
    }

    /**
     * This method is for analysis use to get a list of stores offered by the application.  The store itself can be obtained using the name and suffix with store
     * @return array of string representing the keys to access the store.
     */
    public function getStoreNameList()
    {
        return array_keys($this->datastores);
    }

    /**
     * This method is used to start a CLI based job from the current process itself.  It is useful to introduce concurrency into
     * the application logics for long running process.
     *
     * @param  string  $jobfileName     The file name of the job to run.  It is ASSUMED to be in the job folder.
     * @param  array   $params          Parameters to pass over to the job in question
     * @param  boolean $runInBackground Whether to run this job in the background.  I.e.  non-blocking and will immediately return.  Otherwise
     *                                  the parent process will have to wait for the job to complete before the results returned
     * @return string                   Any output captured
     */
    public function startCLIJob($jobfileName, $params, $runInBackground = true)
    {
        if ($this->app) {
            $app = $this->app;
        } else {
            $app = App::getInstance();
        }

        $this->log("[AppController]  startCLIJob Parameters: file name to run $jobfileName", SNAP_LOG_DEBUG);
        if (count($params)) {
            $params = http_build_query($params);
        } else {
            $params = '';
        }

        $commandLine = sprintf(
            PHP_BINDIR . "/php -q %scli.php -f %sjob/%s -c %s -p \"%s\"",
            $app->getLibPath(),
            $app->getLibPath(),
            basename($jobfileName),
            $app->getConfigFile(),
            $params
        );
        if ($runInBackground) {
            $commandLine .= " > /dev/null &";
        }
        $handle = popen($commandLine, 'r');
        $output = '';
        if (! $runInBackground) {
            while (! feof($handle)) {
                $output .= fread($handle, 1024);
            }
        }
        pclose($handle);
        $this->log("[AppController]  Running CLI job with command line: $commandLine, returned output $output", SNAP_LOG_DEBUG);
        return $output;
    }

    /**
     * This method is used to start a script command based job from the current process itself.  
     *
     * @param  string  $shellCommand     Full path to the shell script to run.
     * @param  array   $params           Array of arguments to pass.  E.g.  can be [ '-f' => 'abc', '-k setting2' ]
     * @param  boolean $runInBackground Whether to run this job in the background.  I.e.  non-blocking and will immediately return.  Otherwise
     *                                  the parent process will have to wait for the job to complete before the results returned
     * @return string                   Any output captured
     */
    public function startShellScript($shellCommand, $params = null, $runInBackground = false)
    {
        if ($this->app) {
            $app = $this->app;
        } else {
            $app = App::getInstance();
        }
        $this->log("[AppController] startShellScript with command $shellCommand : params count " . (is_array($params)?count($params):'none'), SNAP_LOG_DEBUG);
        if ($params && is_array($params)) {
            foreach($params as $switch => $value) {
                $argument .= (intval($switch)) ? " $value" : " $switch $value";
            }
        } else {
            $argument = '';
        }
        $commandLine = $shellCommand . ' ' . $argument;
        if ($runInBackground) {
            $commandLine .= " > /dev/null &";
        }
        $handle = popen($commandLine, 'r');
        $output = '';
        if (! $runInBackground) {
            while (! feof($handle)) {
                $output .= fread($handle, 1024);
            }
        }
        pclose($handle);
        $this->log("[AppController]  Running shell script with command line: $commandLine, returned output $output", SNAP_LOG_DEBUG);
        return $output;
    }
}
?>