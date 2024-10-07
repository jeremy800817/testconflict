<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap;

if (! defined('SNAPAPP_DIR')) {
    die('SNAPAPP_DIR not defined!');
}
if (! defined('SNAPLIB_DIR')) {
    die('SNAPLIB_DIR not defined!');
}
if (! defined('SNAPAPP_DB_SIGNATURE')) {
    define('SNAPAPP_DB_SIGNATURE', 'snap');
}
if (! defined('SNAPAPP_MODE_BACKOFFICE_OPERATOR')) {
    define('SNAPAPP_MODE_BACKOFFICE_OPERATOR', 1);
}
if (! defined('SNAPAPP_MODE_BACKOFFICE_PARTNER')) {
    define('SNAPAPP_MODE_BACKOFFICE_PARTNER', 2);
}
if (! defined('SNAPAPP_MODE_FUNDIN')) {
    define('SNAPAPP_MODE_FUNDIN', 3);
}
if (! defined('SNAPAPP_MODE_FUNDIN_RETURN')) {
    define('SNAPAPP_MODE_FUNDIN_RETURN', 4);
}
if (! defined('SNAPAPP_MODE_WEBSERVICE')) {
    define('SNAPAPP_MODE_WEBSERVICE', 5);
}
if (! defined('SNAPAPP_MODE_FUNDOUT')) {
    define('SNAPAPP_MODE_FUNDOUT', 6);
}
if (! defined('SNAPAPP_MODE_HUNTOO')) {
    define('SNAPAPP_MODE_HUNTOO', 7);
}
if (! defined('SNAPAPP_MODE_SETTLEMENT_RETURN')) {
    define('SNAPAPP_MODE_SETTLEMENT_RETURN', 8);
}
if (! defined('SNAPAPP_MODE_CLI')) {
    define('SNAPAPP_MODE_CLI', 9);
}

//Constant modes supported by the Snap App framework.  These can be combined with binary |
//E.g.  SNAP_MODE_NO_USERSESSION | SNAP_MODE_CLI
if (! defined('SNAP_MODE_NO_USERSESSION')) {
    define('SNAP_MODE_NO_USERSESSION', 0x01);
}
if (! defined('SNAP_MODE_NO_DB')) {
    define('SNAP_MODE_NO_DB', 0x02);
}

//Can use the following defines to set in the config file.
//define('SNAPAPP_CONFIGFILE', 'xxxxxxx/config2.ini');
//The DB user id to set for all operations that tracks activity
//define('SNAPAPP_DBACTION_USERID', 34);
//
//
require_once(dirname(SNAPLIB_DIR) . '/vendor/autoload.php') ;
include_once(SNAPLIB_DIR . DIRECTORY_SEPARATOR . 'interfaces.php');
include_once(SNAPLIB_DIR . DIRECTORY_SEPARATOR . 'inputexception.php');
Use Snap\controller as Controller;
USe Snap\store\dbdatastore as DbDatastore;
use Monolog\Logger;
use Snap\override\monolog\FilterLogHandler;

define('SNAP_LOG_INFO', 200);
define('SNAP_LOG_DEBUG', 100);
define('SNAP_LOG_NOTICE', 250);
define('SNAP_LOG_WARN', 300);
define('SNAP_LOG_WARNING', 300);
define('SNAP_LOG_ERROR', 400);
define('SNAP_LOG_CRITICAL', 500);
define('SNAP_LOG_ALERT', 550);
define('SNAP_LOG_EMERGENCY', 600);

// start the timer
$GLOBALS['SNAP_LOG_LEVEL'] = -1;
$GLOBALS['_mxtimer_'] = array_sum(explode(' ', microtime()));
$GLOBALS['MX_SESSION_CLIENTIP'] = '';
if (isset($_SERVER['REMOTE_ADDR'])) {
    $GLOBALS['MX_SESSION_CLIENTIP'] = $_SERVER['REMOTE_ADDR'];
} else {
    $_SERVER['REMOTE_ADDR'] = '';
}

/**
 * Main singleton application class. This class can be accessed anywhere with the call to App::getInstance() method.
 *
 * @author Devon Kog <devon@silverstream.my>
 * @version 1.0
 * @package snap.base
 */
class App
{
    Use TLogging;  //Logging traits

    /**
    * Singleton instance
    *
    * @var      mxApp
    */
    protected static $instance = null;

    /**
    * The Config object that points to the config file
    *
    * @var      Config
    */
    protected $config = null;

    /**
    * The Config object that points to the config file
    *
    * @var      Config
    */
    private $operationMode = 0;

    /**
    * The UserSession object
    *
    * @var      UserSession
    */
    protected $userSession = null;

    /**
    * The path and filename of the config file to load
    *
    * @var      string
    */
    protected $configFile = '';

    /**
    * The current language name of the app
    *
    * @var      string
    */
    protected $language = 'en_us.utf-8';

    /**
     * The PHPMailer\PHPMailer\PHPMailer object to be used for sending emails.
     * @var null
     */
    protected $mailer = null;

    protected $mode = 0;

    protected $projectBase = null;

    /**
     * This is the application specific controller that will initialise all required
     * application services
     *
     * @var IAppController
     */
    protected $controller = null;

    /**
     * Indicates if we should allow the controller to be called without using the getController() method in app objct.
     * @var boolean
     */
    private $controllerCallableFromApp = false;

    /**
     * Role based access control module object
     * @var null
     */
    private $rbac = null;
    
    /**
    * Timezone the app should display time in
    *
    * @var 		\DateTimeZone
    */
    protected $userTimezone = null;

    /**
    * Timezone the app should display time in
    *
    * @var 		\DateTimeZone
    */
    protected $serverTimezone = null;

    /**
    * Official name of this application
    *
    * @var string
    */
    protected $appname = 'Generic';

    /**
    * Official tag line of this application
    *
    * @var string
    */
    protected $tagline = 'A Pie For All';

    /**
    * Release version of this application
    *
    * @var string
    */
    protected $version = '2.0.0RC1';

    /**
    * Development release of this application
    *
    * @var string
    */
    protected $release = '';

    /**
    * Date of current release
    *
    * @var string
    */
    protected $dateReleased = '2015-09-23';

    /**
    * Copyright statement
    *
    * @var string
    */
    protected $copyright = 'Copyright (c) %s. (r) All rights reserved.';

    /**
    * The memory cacher object
    *
    * @var Cacher
    */
    protected $cacher = null;

    /**
    * The system db object handle
    *
    * @var \PDO
    */
    protected $dbHandle = null;

    /**
    * The indicator for CLI environment
    *
    * @var integer
    */
    protected $bCLI = false;

    private function __construct($config, $operatingMode)
    {
        $this->configFile = $config;
        $this->config = new config($this->configFile);  //Config::getInstance($this->configFile);
        $bReloadCache = ((isset($_GET['rc']) && 1 == $_GET['rc'])? true : false);
        if ($bReloadCache || ! $this->config->loadFromCache()) {
            if (! $this->config->load()) {
                throw new Exception(sprintf(gettext("Failed to open configure file at %s"), $this->configFile));
            }
        }
        $this->operationMode = $operatingMode;
        //Initialise the app controller extention so that it is callable
        if (0 < strlen($this->config->{'snap.class.appController'})) {
            $this->controller = new $this->config->{'snap.class.appController'};
        } else {
            $this->controller = new Controller;
        }
        if (! $this->controller instanceof IAppController) {
            $this->log(__CLASS__ . " - Unable to get the application controller because it does not implement IAppController", SNAP_LOG_ERROR);
            throw new \Exception("Unable to get the application controller because it does not implement IAppController.");
        }
        $this->controllerCallableFromApp = $this->controller->isCallableFromApp();

        return $this;
    }

    public static function getInstance($config = '', $operatingMode = 0)
    {
        if (! self::$instance) {
            self::$instance = new self($config, $operatingMode);
        }
        return self::$instance;
    }

    public function __destruct()
    {
        //session_write_close();
    }

    public function shutdown()
    {
        if (method_exists($this->controller, 'shutdown')) {
            return $this->controller->shutdown();
        }
    }

    public function getConfigFile()
    {
        return $this->configFile;
    }
    
    public static function getRemoteIP()
    {
        return Common::getRemoteIP();
    }

    public static function whereami($name)
    {
        $data = debug_backtrace();
        $data = $data[0];
        echo  htmlspecialchars(sprintf("%s [%d]: %s<br>", isset($data['class']) && 0 < strlen($data['class']) ? $data['class'] : $data['file'], $data['line'], $name));
    }

    public static function errorHandler($errno, $errstr, $errfile, $errline)
    {
        $me = self::getInstance();

        switch ($errno) {
            case E_ERROR:
            case E_PARSE:
            case E_CORE_ERROR:
            case E_CORE_WARNING:
            case E_COMPILE_ERROR:
            case E_COMPILE_WARNING:
            case E_USER_ERROR:
            case E_USER_WARNING:
                if ($me) {
                    $me->log("PHP ERROR =========================", SNAP_LOG_ERROR);
                    $me->log("Fatal PHP Error [$errno] $errstr", SNAP_LOG_ERROR);
                    $me->log("PHP ERROR =========================", SNAP_LOG_ERROR);
                } else {
                    echo "<b>My ERROR</b> [$errno] $errstr<br />\n";
                    echo "  Fatal error on line $errline in file $errfile";
                    echo ", PHP " . PHP_VERSION . " (" . PHP_OS . ")<br />\n";
                    echo "Aborting...<br />\n";
                }
                exit(1);
                break;
            case E_USER_WARNING:
                //echo "<b>My WARNING</b> [$errno] $errstr<br />\n";
                break;

            case E_USER_NOTICE:
                //echo "<b>My NOTICE</b> [$errno] $errstr<br />\n";
                break;

            default:
                //echo "Unknown error type: [$errno] $errstr<br />\n";
                break;
                }
        /* Don't execute PHP internal error handler */
        return true;
    }

    public static function shutdownErrorHandler()
    {
        if (is_null($e = error_get_last()) === false) {
            $errorToReport = E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED;
            if ($e['type'] & $errorToReport) {
                if (self::getInstance()) {
                    ob_start();
                    debug_print_backtrace();
                    $trace = ob_get_contents();
                    ob_end_clean();
                    self::getInstance()->log('Fatal PHP error: ' . print_r($e, true) . "\nStack Trace: $trace", SNAP_LOG_ERROR);
                } else {
                    mail('devon@silverstream.my', 'Error from auto_prepend', print_r($e, true));
                }
            }
        }
    }

    /**
    * Get the current the stop watch time since the last self::startStopWatch() call
    *
    * @return   float The time elapsed since the last self::startStopWatchTime() call
    */
    public static function getCurrentTimer()
    {
        //$endTime = Common::getMicroTime();
        return Common::getMicroTime() - $GLOBALS['_mxtimer_'];
    }

    /**
    * Get the path that points to the MX library
    *
    * @return string Path name that points to the MX library
    */
    public function getLibPath()
    {
        return SNAPLIB_DIR . DIRECTORY_SEPARATOR;
    }

    /**
    * Get the path that points to the MX Application
    *
    * @return string Path name that points to the MX Application
    */
    public function getAppPath()
    {
        return SNAPAPP_DIR . DIRECTORY_SEPARATOR;
    }

    /**
    * Send a log string into the logfile with information of time taken and memory used to arrived at the checkpoint
    *
    * @param	string $name Name of the checkpoint
    * @param 	int $priority priority level at which to log.
    *
    * @return void
    */
    public function setCheckPoint($name, $priority = SNAP_LOG_INFO)
    {
        if ($this->getLogLevel() >= $priority) {
            $msg = 'Checkpoint #('.$name.'): time to arrive checkpoint = '.number_format($this->getCurrentTimer(), 5).' seconds';
            if (function_exists('memory_get_usage')) {
                $msg .= ', memory utilized = '.Common::sizeFormat(memory_get_usage());
            }
            $this->log($msg, $priority);
        }
    }


    /**
    * Get the name of the app
    *
    * @return   string
    */
    public function getAppName()
    {
        if ($this->getConfig()->isKeyExists('systemname')) {
            $this->appname = $this->getConfig()->systemname;
        }
        return $this->appname;
    }

    /**
    * Get the tag line of the app
    *
    * @return   string
    */
    public function getAppTagLine()
    {
        return $this->tagline;
    }

    /**
    * Get the version of the app
    *
    * @return   string
    */
    public function getVersion()
    {
        return $this->version;
    }

    /**
    * Get the release of the app
    *
    * @return   string
    */
    public function getRelease()
    {
        return $this->release;
    }

    /**
    * Get the copyright notice of the app
    *
    * @return   string
    */
    public function getCopyright()
    {
        return str_replace(array('(c)', '(r)'), array('&copy;', '&reg;'), sprintf($this->copyright, $this->getAppName()));
    }

    /**
    * Get the date release of the app
    *
    * @param string $format Format of the date string to return in
    *
    * @return   string
    */
    public function getReleaseDate($format = 'F j, Y')
    {
        $date = new \Datetime($this->dateReleased);
        return $date->format($format);
    }

    /**
    * Get the full app name of the app
    *
    * @return   string
    */
    public function getFullAppName()
    {
        $appname = $this->getAppName();
        if ('' == $this->getRelease()) {
            return $appname.' v'.$this->getVersion();
        }
        return $appname.' v'.$this->getVersion().' ('.$this->getRelease().')';
    }

    /**
    * Get the current log level. Value can be one of the constants defined:
    * SNAP_LOG_DEBUG, SNAP_LOG_INFO, SNAP_LOG_NOTICE, SNAP_LOG_WARNING, SNAP_LOG_ERROR, SNAP_LOG_CRITICAL, SNAP_LOG_ALERT, SNAP_LOG_EMERGENCY
    *
    * @return	int The current log level. -1 if there is no log level set
    */
    public function getLogLevel()
    {
        return $GLOBALS['SNAP_LOG_LEVEL'];
    }

    /**
     * This method will return the logger object that can then be used directly e.g.  to set different channel ID
     * E.g.  $newLogger = $app->getLogger()->withName('fundin');
     *
     * @return \Monolog\Logger
     */
    public function getLogger()
    {
        return $this->logger;
    }
    /**
    * Set the current log level. Value can be one of the constants defined:
    * SNAP_LOG_DEBUG, SNAP_LOG_INFO, SNAP_LOG_NOTICE, SNAP_LOG_WARNING, SNAP_LOG_ERROR, SNAP_LOG_CRITICAL, SNAP_LOG_ALERT, SNAP_LOG_EMERGENCY
    *
    * @param	int $loglevel Set the desired log level
    * @return	void
    */
    public function setLogLevel($loglevel)
    {
        $GLOBALS['SNAP_LOG_LEVEL'] = $loglevel;
    }

    /**
    * Get the current base URL (http[s]://HTTP_HOST[:SERVER_PORT]/SCRIPT_NAME) that's use to access this page
    *
    * @param boolean $bCompat If true, it will only return URL of such format HTTP_HOST[:SERVER_PORT]/ + dirname(SCRIPT_NAME) (always with a trailing '/')
    *
    * @return string The complete URL construct that is used to accessed this page (excluding query string)
    */
    public function getBaseURL($bCompat = false)
    {
        return Common::getBaseURL($bCompat);
    }

    /**
    * Get the actual full URL used to access this page
    *
    * @return string The complete URL construct that is used to access this page (together with query string)
    */
    public function getFullURL()
    {
        return Common::getFullURL();
    }

    /**
     * Returns the config object for use.
     * @return Snap\Config
     */
    public function getConfig()
    {
        return $this->config;
    }
    /**
    * Check whether the this framework is currently running within a session
    *
    * @return boolean True if it is. Otherwise false
    */
    public function isSession()
    {
        if ($this->userSession == null) {
            return false;
        }
        return true;
    }

    /**
    * Set the current timezone of the app
    *
    * @param mixed $timezone String id or \DateTimeZone object of the timezone value to set (eg. Asia/Kuala_Lumpur)
    *
    * @return boolean True if successful. Otherwise false
    */
    private function setTimeZone($timezone = '', $forUser = false)
    {
        if ((! $timezone instanceof \DateTimeZone) && 0 <= strlen($timezone)) {
            $timezone = str_replace(' ', '_', $timezone);
            if (0 == strlen($timezone)) {
                $timezone = date_default_timezone_get();
            }
            try {
                $timezone = new \DateTimeZone($timezone);
            } catch (\Exception $e) {
                $this->log('There is an error in timezone value "'.$timezone.'": '.$e->getMessage(), SNAP_LOG_ERROR);
                return false;
            }
        }
        if ($timezone instanceof \DateTimeZone) {
            if ($forUser) {
                $this->userTimezone = $timezone;
            } else {
                $this->serverTimezone = $timezone;
                date_default_timezone_set($timezone->getName());
            }
            $this->log(($forUser?'User' : 'Server') .' timezone is now set to '.$timezone->getName(), SNAP_LOG_DEBUG);
            return true;
        }
        $this->log('Failed setting timezone for ['.$timezone.']...', SNAP_LOG_ERROR);
        return false;
    }

    public function setUserTimezone($timezone)
    {
        return $this->setTimeZone($timezone, true);
    }
    
    public function setServerTimezone($timezone)
    {
        return $this->setTimeZone($timezone, false);
    }

    /**
    * Get the current timezone of the app
    *
    * @return \DateTimeZone
    */
    private function getTimeZone($forUser = false)
    {
        $timezone = ($forUser && null != $this->userTimezone) ? $this->userTimezone : $this->serverTimezone;
        if ($timezone === null) {
            $this->setTimeZone('', $forUser);
        }
        return $timezone;
    }

    public function getUserTimezone()
    {
        return $this->getTimeZone(true);
    }

    public function getServerTimezone()
    {
        return $this->getTimeZone(false);
    }

    public function setLogPath($path)
    {
        $this->logPath = $path;
        if (! preg_match('#/$#', $this->logPath)) {
            $this->logPath .= '/';
        }
    }

    /**
    * Get the path that stores the log files
    *
    * @return string
    */
    public function getLogPath()
    {
        if (! empty($this->logPath) && ! preg_match('#/$#', $this->logPath)) {
            $this->logPath .= '/';
        }
        return $this->logPath;
    }

    /**
    * Initialize and create a log singleton object
    *
    * @param string $logPrefix Prefix string to the log filename
    * @param integer $logLevel Level of details to log (Default: SNAP_LOG_ERROR)
    *
    * @return boolean True if sucessful. Otherwise false
    */
    public function initLogger($logName, $logLevel = \Monolog\Logger::ERROR, $bUseSession = true)
    {
        $this->setLogLevel($logLevel);

        // -1 - means to disable log
        if ($this->getLogLevel() == -1) {
            return true;
        }

        // if log path has not been create, create it now
        $logPath = $this->getLogPath();
        if (! file_exists($logPath)) {
            mkdir($logPath, 0644);
            if (! file_exists($logPath)) {
                throw new Exception(sprintf(gettext("Failed to create log path directory %s"), $logPath));
            }
        }
        // initialize the logger instance
        $me = $this;
        $this->logger = new \Monolog\Logger('snap');
        $this->logger->pushProcessor(function ($record) Use ($bUseSession, $me) {
            if ($bUseSession) {
                $record['extra']['ip'] = $me->getRemoteIP();
            } else {
                $record['extra']['ip'] = 'CLI';
            }
            return $record;
        });
        //Customise the line formatter to our used to format
        $formatter = new \Monolog\Formatter\LineFormatter("[%datetime%] [%channel%.%level_name%] (%extra.ip%) %message% %context%\n", null, true, true);
        //Create an file output stream to redirect data....
        $stream = new \Monolog\Handler\StreamHandler($logPath.$logName.'.log', $logLevel);
        $stream->setFormatter($formatter);
        $this->logger->pushHandler($stream);

        if (! $this->logger) {
            // unable to create a log singleton object...
            throw new Exception("Unable to initialise the logging component");
        }

        $this->logDebug('class '.get_class($this).' starts with logger enabled ['.number_format($this->getCurrentTimer(), 5).']', SNAP_LOG_DEBUG);
        return true;
    }

    /**
    * This will check the config for any IP access control settings
    * and compare it against the client IP for access restriction
    *
    * @return boolean True if successful. Otherwise false
    */
    public function isAccessAllowed()
    {
        // always allow access if server and client ips are the same
        if ($_SERVER['REMOTE_ADDR'] == $_SERVER['SERVER_ADDR']) {
            return true;
        }

        // The allowFrom directives are evaluated before the denyFrom directives.
        // Access is denied by default. Any client which does not match an allowFrom
        // directive or does match a denyFrom directive will be denied access to the server.
        $config = $this->getConfig();
        if ($config !== null) {
            if ($config->isKeyExists('allowFrom')) {
                $ips = explode(',', $config->allowFrom);
                foreach ($ips as $ip) {
                    $ip = trim($ip);
                    if (! empty($ip)) {
                        if ('all' == $ip || Common::wildcardCmpi($_SERVER['REMOTE_ADDR'], $ip)) {
                            return true;
                        }
                    }
                }
            }
            if ($config->isKeyExists('denyFrom')) {
                $ips = explode(',', $config->denyFrom);
                foreach ($ips as $ip) {
                    $ip = strtolower(trim($ip));
                    if (! empty($ip)) {
                        if ('all' == $ip || Common::wildcardCmpi($_SERVER['REMOTE_ADDR'], $ip)) {
                            $this->setLastError(MX_ERR_ACCESS_DENIED);
                            $this->log('Access has been denied to '.$_SERVER['REMOTE_ADDR'].' based on the access control settings', SNAP_LOG_WARNING);
                            return false;
                        }
                    }
                }
            }
        }
        return true;
    }


    /**
    * This function initialize all necessary objects to prepare
    * the engine for usage of the library and content
    *
    * @param boolean $bSession Whether to start the web session management (Default: true)
    * @param boolean $bCLI Whether this session is in CLI mode (Default: false)
    *
    * @return void
    */
    public function init($bSession = true)
    {
        $configFile = realpath('config.ini');
        if (defined('SNAPAPP_CONFIGFILE')) {
            $this->configFile = SNAPAPP_CONFIGFILE;
        }
        $GLOBALS['MX_SESSION_CLIENTIP'] = Common::getRemoteIP();
        if (0 == strlen($this->configFile)) {
            $this->configFile = SNAPAPP_DIR . DIRECTORY_SEPARATOR. 'config.ini';
        }
        if ($this->config->isKeyExists('specialMode')) {
            if (1 == $this->config->specialMode) {
                $this->isSpecialMode = true;
                $this->obsfucationParamName = $this->config->obsfucationParamName;
            }
        }

        // different logName for different environment (with or without session)
        $logPath = SNAPAPP_DIR . DIRECTORY_SEPARATOR . 'logs';
        $logName = 'snap';
        $logLevel = 3;

        // get the logPath from the config file
        if ($this->config->isKeyExists('snap.log.Path')) {
            $logPath = $this->config->{'snap.log.Path'};
            if (! preg_match('/^\//', $logPath) && ! preg_match('/^[a-z]:\//i', $logPath)) {
                $logPath = $this->getAppPath() . $logPath;
            }
        }
        $this->setLogPath($logPath);

        // set the log level if the config has such thing defined
        if ($this->config->isKeyExists('snap.log.Level')) {
            $logLevel = intval($this->config->{'snap.log.Level'});
        }
        $this->config->{'snap.log.Level'} = $logLevel;
        // set the log level if the config has such thing defined
        if ($this->config->isKeyExists('snap.log.Name')) {
            $logName = $this->config->{'snap.log.Name'};
        }
        $logLevelMap = [SNAP_LOG_EMERGENCY, SNAP_LOG_ALERT, SNAP_LOG_CRITICAL, SNAP_LOG_ERROR, SNAP_LOG_WARNING, SNAP_LOG_NOTICE, SNAP_LOG_INFO, SNAP_LOG_DEBUG];
        $logLevel = $logLevelMap[$logLevel];
        
        // Initialize and create a log singleton object
        if (! $this->initLogger($logName, $logLevel, $bSession)) {
            return false;
        }
        $this->config->{'snap.log.Path'} = $logPath;
        $this->config->{'snap.log.Name'} = $logName;
        $this->config->{'snap.log.Level'} = $logLevel;

        // set to the user defined error handler
        set_error_handler(array( 'Snap\App', 'errorHandler' ));
        //set_exception_handler( array( 'mxApp', 'exceptionHandler' ) );
        register_shutdown_function(array( 'Snap\App', 'shutdownErrorHandler' ));

        // setting the reference (default) timezone (which is the server timezone)
        // the server timezone can be obtained either from the config.ini from key 'snap.timezone.Server' or the environment variable 'TZ'
        $timezone = '';
        if ($this->config->isKeyExists('snap.timezone.Server') && 0 < strlen($this->config->{'snap.timezone.Server'})) {
            $timezone = str_replace(' ', '_', $this->config->{'snap.timezone.Server'});
            $this->log('Setting system timezone to "'.$timezone.'" (config.ini)...', SNAP_LOG_DEBUG);
            $this->setServerTimezone($timezone);
        } else {
            $timezone = getenv('TZ');
            if ($timezone !== false) {
                $timezone = str_replace(' ', '_', $timezone);
                $this->log('Setting system timezone to "'.$timezone.'" (TZ ennvironment)...', SNAP_LOG_DEBUG);
                $this->setServerTimezone($timezone);
            }
        }

        // // get the localized application timezone environment (specific for each website)
        // // if don't have will follow the server timezone
        // if ($this->config->isKeyExists('timezone') && 0 < strlen($this->config->timezone)) {
        // 	$timezone = str_replace(' ', '_', $this->config->timezone);
        // 	//if (date_default_timezone_get() != $this->config->timezone) {
        // 		$this->setTimeZone($timezone);
        // 	//}
        // } else {
        // 	$this->setTimeZone();
        // }

        if ($this->config->isKeyExists('snap.log.showsql') && preg_match('/on|true/i', $this->config->{'snap.log.showsql'})) {
            define('SNAP_APP_LOGSQL', true);
        }

        // $memcachedServers = array();
        $module = $this->config->{'snap.cache.Type'};
        $cacheId = $this->config->isKeyExists('snap.cache.id') ? $this->config->{'snap.cache.id'} : '0';
        $servers = $this->config->isKeyExists('snap.cache.servers') ? $this->config->{'snap.cache.servers'} : $this->config->{'snap.cache.memcachedServers'};
        $this->log("Initialising $module cache system with Id $cacheId and servers $servers", SNAP_LOG_DEBUG);
        $this->cacher = \Snap\cacher::getInstance($cacheId, $module, $servers);
        //Test drive if the cacher is working or not first.....
        $data = $this->cacher->get('test_cacher');
        if (! $data) {
            $this->cacher->set('test_cacher', 1);
            if (! $this->cacher->get('test_cacher')) {
                $this->logError("Unable to initialise the cache system Cacher::getInstance($cacheId, '$module', '$servers')");
                throw new \Exception('Unable to initialise the cache system');
            }
        }

        if ($bSession) {
            session_start();
        }
        return true;
    }

    public function getCacher()
    {
        return $this->cacher;
    }

    /**
     * This method will implement a checking to make sure that the user logging in has authorization.
     */
    private function isAuthorizedLogin()
    {
        return true;
    }

    public function isSpecialMode()
    {
        return $this->isSpecialMode;
    }

    public function getObsfucationParamName()
    {
        return $this->obsfucationParamName;
    }

    /**
    * Store the object generated variables as cache
    *
    * @param string $keys The key index to cache the data under.
    *					  Same data can be cached under more than one key, just separate each key with '<>'
    * @param mixed $data The data to cache. This parameter is passed by reference
    * @param integer $type (Optional) Type of caching mechanism. (Default: MX_CACHETYPE_TEMPORARY)
    *						MX_CACHETYPE_TEMPORARY - cache the data on a per HTTP request basis (single instance memory allocation)
    *						MX_CACHETYPE_SESSION - cache the data on a user session specific basis (session management)
    *						MX_CACHETYPE_PERSISTENT - cache the data on a persistent basis across all HTTP requests (shared memory)
    * @param integer $expire (optional) Indicate in seconds how the cache is good before expiring. (Default: 0 - never expire, -1 - don't cache)
    *
    * @return   void
    */
    public function setCache($key, $data, $expire = 0)
    {
        if ($this->cacher == null) {
            return;
        }
        $this->cacher->set($key, $data, $expire);
    }


    /**
    * Retrieve the object generated variables from the cache by object type and key
    *
    * @param string $key The cache key index to retrieve the data from the cache
    *
    * @return   mixed The cached data on success. Otherwise null.
    */
    public function getCache($key)
    {
        if ($this->cacher == null) {
            return null;
        }
        $data = $this->cacher->get($key);
        return $data;
    }

    /**
    * Remove the cached data by $key
    *
    * @param string $key The cache key index to delete the data from the cache
    *
    * @return   void
    */
    public function delCache($key)
    {
        if ($this->cacher == null) {
            return;
        }
        $this->cacher->del($key);
    }

    /**
    * Reset or remove the cached data by $key
    *
    * @param string $mask The cache key mask to delete the data from the cache. (Default: '*' which means all cache)
    *
    * @return   void
    */
    public function resetCache($mask = '*', $type = MX_CACHETYPE_PERSISTENT)
    {
        if ($this->cacher == null) {
            return;
        }
        $this->log('Resetting cache for mask "'.$mask.'" (type: '.$type.')', SNAP_LOG_DEBUG);
        $this->cacher->delAll($mask);
    }

    /**
     * Database initialisation based on the configuration
     *
     * @return boolean True on success. Otherwise false
     */
    public function initSystemDB()
    {
        try {
            // initialize the 'system' db singleton
            $this->dbHandle = new db($this->config->{'snap.db.Type'}, $this->config->{'snap.db.Host'}, $this->config->{'snap.db.Username'}, $this->config->{'snap.db.Password'}, $this->config->{'snap.db.Name'}, $this->getCharset(), $this->config->{'snap.db.ssl.key'}, $this->config->{'snap.db.ssl.cert'}, $this->config->{'snap.db.ssl.ca'});
            if (null == $this->dbHandle) {
                // unable to create a db singleton object that connects to the system db
                throw new \Exception($this->config->{'snap.db.Type'}.'://'.$this->config->{'snap.db.Username'}.':xxxxxxx@'.$this->config->{'snap.db.Host'}.'/'.$this->config->{'snap.db.Name'});
            }
        } catch(\PDOException $e) {
            $this->log("Error connecting to DB - " .$this->config->{'snap.db.Type'}.'://'.$this->config->{'snap.db.Username'}.':xxxxxxx@'.$this->config->{'snap.db.Host'}.'/'.$this->config->{'snap.db.Name'}.'with error ' . $e->getMessage(), SNAP_LOG_ERROR);
            throw new \Exception("Error connecting to database " . $this->config->{'snap.db.Type'}.'://'.$this->config->{'snap.db.Username'}.':xxxxxxx@'.$this->config->{'snap.db.Host'}.'/'.$this->config->{'snap.db.Name'}.' with error ' . $e->getMessage());        
        }
        return true;
    }

    public function getDBHandle()
    {
        return $this->dbHandle;
    }

    public function setDBActionBy($userId)
    {
        if ($this->dbHandle != null) {
            $this->dbHandle->query('SET @actionBy = '.$userId);
        }
    }

    public function setDBActionRealBy($userId)
    {
        if ($this->dbHandle != null) {
            $this->dbHandle->query('SET @actionRealBy = '.$userId);
        }
    }

    public function run($contextOrMode, $username, $password, $captcha, $extraParams)
    {
        //1.  Initialisation of the logs, session and cache services
        $this->init(! ($contextOrMode & SNAP_MODE_NO_USERSESSION));
        $this->log("Running " . __METHOD__."($contextOrMode, $username, ".str_repeat('X',strlen($password)) .", $captcha, ".json_encode($extraParams).")", SNAP_LOG_DEBUG);

        //2.  Initialise the system database
        if (! ($contextOrMode & SNAP_MODE_NO_DB)  && ! $this->initSystemDB()) {
            $this->log(__CLASS__ . " - Unable to initialise the database", SNAP_LOG_ERROR);
            throw new \Exception("Unable to initialise the system database.  Application not able to continue.");
        }

        //3.  Initialise the application controller
        if (! $this->controller->initialiseController($this, $contextOrMode)) {
            $this->log(__CLASS__ . " - Unable to initialise the app controller class properly", SNAP_LOG_ERROR);
            throw new \Exception("Unable to initialise the app controller class properly.");
        }
        
        //4.  Initialise the session if available and initialise user timezone info.
        if (! ($contextOrMode & SNAP_MODE_NO_USERSESSION)) {
            $isUserSessionSet = $this->setUserSession($username, $password, $captcha, $extraParams);
        }

        $timeZone = '';
        if ($this->isSession() && 0 != $this->getUserSession()->getTimeZoneOffset()) {
            $offset = -1 * $this->getUserSession()->getTimeZoneOffset();
            $timeZone = timezone_name_from_abbr('', intval($offset) * 60, 0);
            $this->log("Timezone source from usersession $offset is now set to " . print_r($timeZone, true), SNAP_LOG_DEBUG);
        } elseif ($this->config->isKeyExists('snap.timezone.user') && 0 < strlen($this->config->{'snap.timezone.user'})) {
            $timeZone = str_replace(' ', '_', $this->config->{'snap.timezone.user'});
            $this->log("Timezone source from config.ini timezone key is now set to $timeZone", SNAP_LOG_DEBUG);
        } elseif ($this->config->isKeyExists('snap.timezone.server') && 0 < strlen($this->config->{'snap.timezone.server'})) {
            $timeZone = str_replace(' ', '_', $this->config->{'snap.timezone.server'});
            $this->log("Timezone source from config.ini snap.timezone.Server key is now set to $timeZone", SNAP_LOG_DEBUG);
        } else {
            $timeZone = getenv('TZ');
            if ($timeZone !== false) {
                $timeZone = str_replace(' ', '_', $timeZone);
                $this->log('Timezone source from TZ ennvironment is now set to "'.$timeZone, SNAP_LOG_DEBUG);
            }
        }
        //Next is to synchronise the date time for all.
        $this->setUserTimeZone($timeZone);
        $now = new \DateTime("now", $this->getUserTimezone());
        $mins = $now->getOffset() / 60;
        $sgn = (0 > $mins ? -1 : 1);
        $mins = abs($mins);
        $hrs = floor($mins / 60);
        $mins -= $hrs * 60;
        $offset = sprintf('%+d:%02d', $hrs*$sgn, $mins);
        // $this->dbHandle->exec("SET time_zone='$offset';");
        
        if (! ($contextOrMode & SNAP_MODE_NO_USERSESSION)) {
            //4.  Get the request router object.
            $requestRouter = null;
            $routeType = (0<strlen($this->config->{'snap.requestrouter.handlerkey'})) ? $this->config->{'snap.requestrouter.type'} : RequestRouter::ROUTE_BY_QUERY;
            $handlerKey = (0<strlen($this->config->{'snap.requestrouter.handlerkey'})) ? $this->config->{'snap.requestrouter.handlerkey'} : 'hdl';
            $actionKey = (0<strlen($this->config->{'snap.requestrouter.handlerkey'})) ? $this->config->{'snap.requestrouter.actionkey'} : 'action';
            $loginHandler = (0<strlen($this->config->{'snap.requestrouter.handlerkey'})) ? $this->config->{'snap.requestrouter.handler.login'} : 'login';
            $expiredHandler = (0<strlen($this->config->{'snap.requestrouter.handlerkey'})) ? $this->config->{'snap.requestrouter.handler.expired'} : 'expired';
            $startupHandler = (0<strlen($this->config->{'snap.requestrouter.handlerkey'})) ? $this->config->{'snap.requestrouter.handler.startup'} : 'startup';
            if (0 < strlen($this->config->{'snap.class.requestrouter'})) {
                $requestRouter = new $this->config->{'snap.class.requestrouter'}($routeType, $handlerKey, $actionKey, $loginHandler, $expiredHandler, $startupHandler);
                $this->controller->initialiseRequestRouter($requestRouter, $contextOrMode);
            } else {
				$requestRouter = new RequestRouter($routeType, $handlerKey, $actionKey, $loginHandler, $expiredHandler, $startupHandler);
                $this->controller->initialiseRequestRouter($requestRouter, $contextOrMode);
            }
            if (! $requestRouter instanceof RequestRouter) {
                $this->log(__CLASS__ . " - Unable to initialise the request router object properly", SNAP_LOG_ERROR);
                throw new \Exception("Unable to initialise the request router object properly.");
            }
            if (! $isUserSessionSet && (0 < strlen($username) && 0 < strlen($password))) {
                $this->log(__METHOD__." - routing to login page", SNAP_LOG_DEBUG);
                //Unable to login - session is already available....
                echo $requestRouter->routeToLoginPage($this);
            // echo $requestRouter->routeToLoginPage($this);
            } elseif ( 8 > count($_REQUEST) && (0 < strlen($username) || 0 < strlen($password))) {
                $this->log(__METHOD__." - routing to the startup page now", SNAP_LOG_DEBUG);
                $this->setDBActionRealBy($this->getUserSession()->getUserId());
                $this->setDBActionBy($this->getUserSession()->getUserId());
                echo $requestRouter->routeToStartupPage($this);
                //echo "<script> window.location.href = location.href; </script>";
                //return;
            } else {
                $this->log(__METHOD__." - routing the request router for further processing", SNAP_LOG_DEBUG);
                $this->setDBActionRealBy($this->getUserSession()->getUserId());
                $this->setDBActionBy($this->getUserSession()->getUserId());
                //5.  Do the routing and collect any of the output to echo.
                $data = $requestRouter->route($this, $contextOrMode);
                if (is_string($data)) {
                    echo $data;
                }
            }
            //6.  Complete the sequence and shutdown the service.
            $this->controller->shutdownController($contextOrMode);
            $this->shutdown();
        } elseif (defined('SNAP_HANDLER_CLASS')) {   //defined
            //Add by Devon on 2017/6/23 to set action by user in audit logs
            $userId = 1;
            if (0 < intVal(SNAPAPP_DBACTION_USERID)) {
                $userId = SNAPAPP_DBACTION_USERID;
            } elseif (0 < strlen($this->getConfig()->{SNAPAPP_DBACTION_USERID})) {
                $userId = $this->getConfig()->{SNAPAPP_DBACTION_USERID};
            }
            $this->setDBActionBy($userId);
            $this->setDBActionRealBy($userId);
            //End Add by Devon on 2017/6/23
            $handlerClass = new \ReflectionClass(SNAP_HANDLER_CLASS);
            $handler = $handlerClass->newInstanceArgs([$this]);
            $handler->doAction($this, $_REQUEST['action'], $_REQUEST);
            $this->controller->shutdownController($contextOrMode);
            $this->shutdown();
        }
    }

    public function getController()
    {
        return $this->controller;
    }

    /**
    * Get the current user session
    *
    * @return mxUserSession mxUserSession object if session is initialized properly. Otherwise null.
    */
    public function getUserSession()
    {
        if ($this->userSession === null) {
            $this->userSession = $this->controller->createUsersession($this);
            if ($this->config->isKeyExists('snap.session.expiredTime')) {
                $this->userSession->setExpiredTime($this->config->{'snap.session.expiredTime'});
            }
        }
        return $this->userSession;
    }

    /**
     * Create a user session or reuse existing session (if $username and $password is not provided)
     *
     * @param string $username (optional)
     * @param string $password (optional)
     * @param string $captcha (optional)
     * @param boolean $isAjaxUI Whether the session is catered for ajax based new UI or not.
     * @param array $extraParams Extra parameters to be passed in validating/authenticating the user session
     *
     * @return void
     */
    public function setUserSession($username = '', $password = '', $captcha = null, $extraParams = array())
    {
        $this->getUserSession();
        if (0 < strlen($username) && 0 < strlen($password)) {
            $captchaType = $this->getConfig()->{"snap.login.usecaptcha"};
        }
        if ("re:captcha" == $captchaType) {
            $recaptcha = new \ReCaptcha\ReCaptcha($this->getConfig()->{"snap.login.recaptcha.secret"});
            $resp = $recaptcha->verify($_REQUEST['g-recaptcha-response'], self::getRemoteIP());
            if ($resp->isSuccess()) {
                // verified!
                // if Domain Name Validation turned off don't forget to check hostname field
                // if($resp->getHostName() === $_SERVER['SERVER_NAME']) {  }
            } else {
                //$errors = $resp->getErrorCodes();
                $this->log("The recaptcha validation failed with errors " . print_r($resp->getErrorCodes(), true), SNAP_LOG_ERROR);
                return false;
            }
        } elseif ('off' != $captchaType && $captcha !== null) {
            $this->log('Verifying captcha code "'.$captcha.'"...', SNAP_LOG_INFO);

            if (isset($_SESSION['captchaPrase'])) {
                $phrase = $_SESSION['captchaPrase'];
            } else {
                $phraseBuilder = new \Gregwar\Captcha\PhraseBuilder;
                $phrase = $phraseBuilder->build(4, '02345689');
                $_SESSION['captchaPrase'] = $phrase;
            }
            $catchaBuilder = new \Gregwar\Captcha\CaptchaBuilder($phrase);
            if (! $catchaBuilder->testPhrase($captcha)) {
                $this->log("The captcha provided {$captcha} is invalid for user $username", SNAP_LOG_ERROR);
                return false;
            }
            $this->log('Captcha code validated successfully.', SNAP_LOG_DEBUG);
        }
        $username = preg_replace('/[^-a-z0-9_@.]/', '', strtolower($username));
        $ret = $this->userSession->login($username, $password, $extraParams);
        if (! $ret) {
            return false;
        }
        return true;
    }

    public function __call($name, $args)
    {
        if ($this->controllerCallableFromApp) {
            if (method_exists($this->controller, $name) || preg_match('/(store|factory|manager)$/i', $name)) {
                return call_user_func_array(array($this->controller, $name), $args);
            } else {
                $this->log(__CLASS__."::{$name} - Unknown method ($name) called!", SNAP_LOG_ERROR);
            }
        }
        return false;
    }

    /**
    * Get the charset code string (eg. iso-8859-1)
    *
    * @return string
    */
    public function getCharset()
    {
        $regs = explode('.', $this->language);
        $charset = trim(strtolower($regs[1]));
        if (empty($charset)) {
            $charset = 'iso-8859-1';
        }
        return $charset;
    }

    /**
     * This method can be used to check if the current user has access to the permission allocated.
     *
     * @param  String  $permission The permission string (concantenated by ;)
     * @return boolean             True if it has access.  False otherwise.
     */
    public function hasPermission($permission)
    {
        $hasAccess = false;
        if (null == $this->rbac) {
            $this->rbac = new \PhpRbac\Rbac;
        }
        if ('/all/access' == $permission && !$this->userSession->isExpired()) {
            return true;
        }
        //Added by Devon on 2017/5/24 to support multiple concatenated permissions (support for combo box remote data access)
        $permArray = explode(';', $permission);
        foreach ($permArray as $aPermission) {
            try {
                if ($this->rbac->check($aPermission, $this->userSession->getUserId())) {
                    $hasAccess = true;
                    break;
                }
            } catch (\Exception $e) {
            }
        }
        //End Add by Devon on 2017/5/24
        return $hasAccess;
    }

    /**
     * This method will return the PHPMailer object to be used for doing emailing.  The settings will have been
     * prefilled so that the object can directly be used by setting recipients and messages , title etc.
     *
     * @return PHPMailer\PHPMailer\PHPMailer Mailer object to be used.
     */
    public function getMailer()
    {
        if (! $this->mailer) {
            $this->mailer = new \PHPMailer\PHPMailer\PHPMailer(true);
            try {
                //Server settings
                $this->mailer->SMTPDebug = 2;
                switch (strtolower($this->config->{'snap.mailer.type'})) {
                    case 'mail':
                        $this->mailer->isMail();
                        break;
                    case 'sendmail':
                        $this->mailer->isSendMail();
                        break;
                    case 'qmail':
                        $this->mailer->isQMail();
                        break;
                    case 'smtp':
                    default:
                        $this->mailer->isSMTP();
                        break;
                }
                $this->mailer->Host = $this->config->{'snap.mailer.host'}; 			    // Specify main and backup SMTP servers
                $this->mailer->Port = $this->config->{'snap.mailer.port'};               // TCP port to connect to
                $this->mailer->SMTPAuth = ('on'==strtolower($this->config->{'snap.mailer.authentication'})) ? true : false; // Enable SMTP authentication
                if ($this->mailer->SMTPAuth) {
                    $this->mailer->Username = $this->config->{'snap.mailer.username'};       // SMTP username
                    $this->mailer->Password = $this->config->{'snap.mailer.password'};       // SMTP password
                    $this->mailer->SMTPSecure = strtolower($this->config->{'snap.mailer.security'});     // Enable TLS encryption, `ssl` also accepted
                }
                $this->mailer->setFrom($this->config->{'snap.mailer.senderemail'}, $this->config->{'snap.mailer.sendername'});
                // $this->mailer->addReplyTo($this->config->{'snap.mailer.senderemail'}, $this->config->{'snap.mailer.sendername'});

                //Recipients
                // $this->mailer->setFrom('from@example.com', 'Mailer');
               //  $mail->addAddress('joe@example.net', 'Joe User');     // Add a recipient
               //  $mail->addAddress('ellen@example.com');               // Name is optional
               //  $mail->addReplyTo('info@example.com', 'Information');
               //  $mail->addCC('cc@example.com');
               //  $mail->addBCC('bcc@example.com');
               //  //Attachments
               //  $mail->addAttachment('/var/tmp/file.tar.gz');         // Add attachments
               //  $mail->addAttachment('/tmp/image.jpg', 'new.jpg');    // Optional name
               //  //Content
               //  $mail->isHTML(true);                                  // Set email format to HTML
               //  $mail->Subject = 'Here is the subject';
               //  $mail->Body    = 'This is the HTML message body <b>in bold!</b>';
               //  $mail->AltBody = 'This is the body in plain text for non-HTML mail clients';
               //  $mail->send();
            } catch (\PHPMailer\PHPMailer\Exception $e) {
                $this->log('App::getMailer() Message could not be sent. Mailer Error: ' . $this->mailer->ErrorInfo, SNAP_LOG_ERROR);
            }
        } else {
            $this->mailer->clearAllRecipients();
            $this->mailer->clearAttachments();
            $this->mailer->setFrom($this->config->{'snap.mailer.senderemail'}, $this->config->{'snap.mailer.sendername'});
        }
        return $this->mailer;
    }

    /**
     * This method is used throughout the application to generate a prefix for any reference number used by the system,
     * @param $module String  The module names to generate a prefix for.
     * @return String  The formatted string
     */
    public function generateRefNoPrefix($module = 'all')
    {
        return date('Ymd');
    }
    
    /**
     * This method will return checker config
     * 
     * @return boolean         True if otc.checker.maker is 'on' and permission is 'true'.  False otherwise.
     */
    public function getOtcChecker ()
    {
        $permission = '/root/system/checker';
        if (preg_match('/(1|on|yes)/i', $this->getConfig()->{'otc.checker.maker'}) && $this->hasPermission($permission)) {
            return true;
        }
        
        return false;
    }
    
    /**
     * This method will return user activity log config
     * 
     * @return boolean         True if otc.user.activity.log is 'on'.  False otherwise.
     */
    public function getOtcUserActivityLog ()
    {
        if (preg_match('/(1|on|yes)/i', $this->getConfig()->{'otc.user.activity.log'})) {
            return true;
        }
        
        return false;
    }
}
?>
