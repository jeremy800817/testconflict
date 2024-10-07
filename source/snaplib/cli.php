<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2017 - 2018
 * @copyright Silverstream Technology Sdn Bhd. 2017 - 2019
 */
Namespace Snap;

define('SNAPAPP_DIR', dirname(dirname(__FILE__)). DIRECTORY_SEPARATOR . 'snapapp' . DIRECTORY_SEPARATOR);
define('SNAPLIB_DIR', dirname(__FILE__) . DIRECTORY_SEPARATOR);
if (! defined('SNAPAPP_DBACTION_USERID')) {
    define('SNAPAPP_DBACTION_USERID', 'system_userid');
}
define('SNAPAPP_PROCESS_MODE', 9);
if(! defined('SNAPAPP_DBACTION_USERID')) {
    define('SNAPAPP_DBACTION_USERID', 3 /* System Job User */);
}
/**
 * This is the main command line interface class that will run the snap framework using commandline.  When invoking
 * this file, you will need to provide the config file to reference as well as a job file.
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.base
 */
class cli
{
    /**
    * The app object
    *
    * @var App
    */
    private $app = null;

    /**
    * The filename and path of where the job class is located
    *
    * @var      string
    */
    private $filename = '';

    /**
    * The class name of the job class where the job is at
    *
    * @var      string
    */
    private $classname = '';

    /**
    * Config file to use
    *
    * @var      string
    */
    private $configFile = true;

    /**
    * Indicates whether program should run in verbose mode or not
    *
    * @var      boolean
    */
    private $bVerbose = true;

    /**
    * The parameters to be passed to job as options
    *
    * @var      array
    */
    private $params = array();

    /**
     * Main method to run the job
     * @return void
     */
    public function run()
    {
        error_reporting(E_ALL & ~E_DEPRECATED & ~E_STRICT & ~E_NOTICE);
        require_once(SNAPLIB_DIR . DIRECTORY_SEPARATOR . 'app.php');
        if (! $this->processOptions()) {
            $this->printInfo();
            return;
        }
        $this->announce("Starting the application instance now....\n");
        $this->app = App::getInstance($this->configFile, SNAPAPP_MODE_CLI);
        // perform app initialization
        $this->announce("Initialising application....\n");
        $this->app->run(SNAP_MODE_NO_USERSESSION, null, null, null, null);
        //Added by Devon on 2014/9/30 to allow for the SNAPAPP_DBACTION_USERID constant as a string referenced in config file.
        $userId = 1;
        if (0 < intVal(SNAPAPP_DBACTION_USERID)) {
            $userId = SNAPAPP_DBACTION_USERID;
        } elseif (0 < strlen($this->app->getConfig()->{SNAPAPP_DBACTION_USERID})) {
            $userId = $this->app->getConfig()->{SNAPAPP_DBACTION_USERID};
        }
        //End Add by Devon on 2014/9/30
        $this->app->setDBActionRealBy($userId);
        try {
            $this->announce("Running the job now....\n");
            $this->runJob();
        } catch (\Exception  $e) {
            $this->app->getLogger()->log(SNAP_LOG_ERROR, 
                sprintf("Uncaught application error @ %s:%d with message %s", 
                        $e->getFile(), 
                        $e->getLine(), 
                        $e->getMessage()));
            $this->announce(sprintf("cli::run() uncaught exception @ %s:%d with message %s", $e->getFile(), $e->getLine(), $e->getMessage()), true);
        }
    }

    private function announce($message, $force = false)
    {
        if ($this->bVerbose || $force) {
            echo $message;
        }
    }

    /**
    * Process all the command line arguments
    *
    * @return boolean Return true if all options are valid. Otherwise false
    */
    private function processOptions()
    {
        $bHelp = false;
        $i = 0;
        foreach ($_SERVER['argv'] as $arg) {
            $arg = trim(strtolower($arg));
            if ('-c' == $arg) {
                $this->configFile = $_SERVER['argv'][$i+1];
            } elseif ('-f' == $arg) {
                $this->filename = $_SERVER['argv'][$i+1];
            } elseif ('-n' == $arg) {
                $this->classname = $_SERVER['argv'][$i+1];
            } elseif ('-p' == $arg) {
                parse_str($_SERVER['argv'][$i+1], $this->params);
            } elseif ('-h' == $arg) {
                $bHelp = true;
            } elseif ('-v' == $arg) {
                if (0 == intval($_SERVER['argv'][$i+1])) {
                    $this->bVerbose = false;
                }
            }
            $i++;
        }
        if (0 == strlen($this->configFile)) {
            $this->announce(sprintf("The config file (%s) is empty\n", $this->configFile));
            $bHelp = true;
        } elseif (! file_exists($this->configFile)) {
            echo "The config file {$this->configFile} is not found";
            return false;
        }
        if (file_exists($this->filename)) {
            require_once($this->filename);
            if (empty($this->classname)) {
                $regs = explode('.', (basename($this->filename)));
                $this->classname = $regs[0];
            }
            if (! class_exists('Snap\\job\\' . $this->classname)) {
                // error! class does not exists...
                if (! class_exists($this->classname)) {
                    // throw new \Exception(sprintf('Unable to load the class (%s) does not exists', $this->classname));
                    $this->announce(sprintf("Unable to load the class (%s) does not exists\n", $this->classname));
                    return false;
                }
            } else {
                $this->classname = 'Snap\\job\\' . $this->classname;
            }
        } else {
            // error! file does not exists...
            // throw new \Exception(sprintf("The script file name (%s) does not exists", $this->filename));
            $this->announce(sprintf("The script file name (%s) does not exists\n", $this->filename));
            return false;
        }
        if ($bHelp) {
            $this->announce("Requested to display the help screen\n");
            return false;
        }
        return true;
    }

    /**
    * Return the help information in using this command line script
    *
    * @return void
    */
    public function printInfo($printJobParamsOnly = false)
    {
        if (! $printJobParamsOnly) {
            echo "**********************************************\n";
            echo "****                                      ****\n";
            echo "**** Snap Command Line Interface Executor ****\n";
            echo "****                                      ****\n";
            echo "**********************************************\n";
            echo sprintf("Usage: %s -c <config_file> -f <file_name> [options] \n", basename($_SERVER['argv'][0]));
            echo "  -c <config_file> 		[Required] Specify configuration file\n";
            echo "  -f <filename>	   		[Required] Full filename with job class (of same filename) to run. Class needs to implement ICliJob interface\n";
            // echo "  -n <classname>	   		The name of the class. If not provided, filename will be used to construct the class name\n";
            echo "  -v <0|1>     			[Optional, default=1] Run in verbose mode (1 = Yes, 0 = No)\n";
            echo "  -h               		Display this help information\n";
            echo "  -p \"<param1..N=value1..N>\"	The parameters to be passed as options to the class specified in \"-f <filename>\"\n";
            echo "                              	Example: \"nameA=valueA&nameB=valueB\" (NOTE: value has to be URL encoded)\n";
        }
        if (class_exists($this->classname)) {
            $job = new $this->classname($this);
            if ($job instanceof ICLIJob) {
                $options = $job->describeOptions();
                $str = $this->classname . " parameters";
                echo sprintf("\n-----%s-----\n", str_repeat('-', strlen($str)));
                echo sprintf("|    %s    |\n", str_repeat(' ', strlen($str)));
                echo         "|    $str    |\n";
                echo sprintf("|    %s    |\n", str_repeat(' ', strlen($str)));
                echo sprintf("-----%s-----\n", str_repeat('-', strlen($str)));
                if (0 == count($options)) {
                    echo "No parameters available for job\n";
                }
                foreach ($options as $option => $prop) {
                    $paramDesc = '';
                    $paramDesc .= $prop['type'];
                    $paramDesc .= (0 < strlen($paramDesc) ? ' ' : '') . ($prop['required'] ? '[Required]' : '[Optional]');
                    $paramDesc .= (0 < strlen($paramDesc) && isset($prop['default']) ? ', Defaults to ' . $prop['default'] : '');
                    echo sprintf("%-15s %s %s\n", $option, '=>', $paramDesc);
                    $paragraphs = explode("\n", $prop['desc']);
                    foreach ($paragraphs as $aParagraph) {
                        $eachWord = explode(' ', $aParagraph);
                        $aSentence = '';
                        foreach ($eachWord as $aWord) {
                            $aSentence .= $aWord . ' ';
                            if (60 < strlen($aSentence)) {
                                echo sprintf("%-15s %s %s\n", str_repeat(' ', strlen($option)), '  ', trim($aSentence));
                                $aSentence = '';
                            }
                        }
                        if (strlen($aSentence)) {
                            echo sprintf("%-15s %s %s\n", str_repeat(' ', strlen($option)), '  ', trim($aSentence));
                        }
                    }
                    echo "\n";
                }
            }
        }
    }

    /**
    * Execute the job involved
    *
    * @return void
    */
    private function runJob()
    {
        $str = "Starting to run CLI job...";
        $this->app->getDbHandle()->query('SET SESSION wait_timeout = 3600;');
        $this->app->getLogger()->log(SNAP_LOG_INFO, $str);
        $this->announce($str."\n");

        $r = new \ReflectionClass($this->classname);
        $job = $r->newInstanceArgs([$this]);

        // $job = new $this->classname($this);
        if ($job instanceof ICLIJob) {
            //Added by Devon on 2017/6/22 to check if all the parameters required for job is provided.
            $jobParamSettings = $job->describeOptions();
            foreach ($jobParamSettings as $jobParamKey => $jobParamProp) {
                if ($jobParamProp['required'] && ! isset($this->params[$jobParamKey])) {
                    $this->announce("***ERROR:  Required job parameter $jobParamKey is not defined\n", true);
                    $this->printInfo(true);
                    return;
                }
            }
            //End Add by Devon on 2017/6/22
            
            $result = $job->doJob($this->app, $this->params);
        } else {
            echo "ERROR: Unable to run the job because it is not an instance of Snap\\ICLIJob\n";
        }
        $str = "...Completed! Time elapsed is ".number_format($this->app->getCurrentTimer(), 4)." secs\n";
        $this->app->getLogger()->log(SNAP_LOG_INFO, $str);
        $this->announce($str."\n");
    }
}

$cli = new cli;
$cli->run();
?>