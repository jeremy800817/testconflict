<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap;

/**
 * Common A class of common functions for mxlib
 *
 * A class of common functions grouped together to be accessed statically.
 *
 * @author Ivan Hoo <ivan@digialliance.com>
 * @version 1.0
 * @package  snap.base
 */

class Common
{

    /**
    * Contructor.
    *
    * The constructor is intentionally left as a private function because it is not supposed to be
    * created.  There will only be a single instance of this class and all its functions is supposed
    * to be static functions.
    *
    * @return void
    */
    private function __construct()
    {
    }

    /**
    * Get the current time in microsecond precision
    *
    * @return float
    * @static
    */
    public static function getBaseClassName($className)
    {
        return array_pop(explode('\\', $className));
    }

    /**
    * Get the current time in microsecond precision
    *
    * @return float
    * @static
    */
    public static function getMicroTime()
    {
        //list($usec, $sec) = explode(' ',microtime());
        //return ((float)$usec + (float)$sec);
        return array_sum(explode(' ', microtime()));
    }

    /**
    * Validates the format of an email address
    *
    * @param string $address email address
    * @return bool
    * @static
    */
    public static function validateEmail($address)
    {
        return (preg_match(
            '/^[-!#$%&\'*+\\.\/0-9=?A-Z^_`a-z{|}~]+'.
                    '@'.
                    '[-!#$%&\'*+\\\/0-9=?A-Z^_`a-z{|}~]+\.'.
                    '[-!#$%&\'*+\\.\/0-9=?A-Z^_`a-z{|}~]+$/',
                    $address
        ));
    }

    public static function validateIP($ip)
    {
        return (preg_match('/^[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}$/i', $ip));
    }

    static public function validateDatetime($date, $strict = true)
    {
        $dateTime = \DateTime::createFromFormat('Y-m-d H:i:s', $date);
        if ($strict) {
            $errors = \DateTime::getLastErrors();
            if (!empty($errors['warning_count'])) {
                return false;
            }
        }
        return false !== $dateTime;
    }

    /**
    * Converts date formatted string into various datetime representation
    *
    * @param string $datestr date formatted string
    * @param integer $mode specify what datetime value to be converted to
    *                      values include 0 - Unix epoch time value
    *                                     1 - SQL time string in datetime format 'YYYY-MM-DD HH:II:SS'
    *                                     2 - sql datetime format 'YYYY-MM-DD'
    *                                     3 - sql datetime format 'HH:II:SS'
    *                                     4 - date time stored in any associative array object
    * @return mixed determined by $mode param, -1 on error
    * @static
    */
    public static function dateToTime($datestr, $mode = 0)
    {
        $hour = $minute = $second = 0;
        $day = $month = 1;
        $year = 1970;
        if (preg_match("/([[:digit:]]+)-([[:digit:]]+)-([[:digit:]]+) ([[:digit:]]+):([[:digit:]]+):([[:digit:]]+)/", $datestr, $regs)) {
            // mysql datetime format (eg. YYYY-MM-DD HH:II:SS)
            $year = $regs[1];
            $month = $regs[2];
            $day = $regs[3];
            $hour = $regs[4];
            $minute = $regs[5];
            $second = $regs[6];
        } elseif (preg_match("/([[:digit:]]+)-([[:digit:]]+)-([[:digit:]]+)/", $datestr, $regs)) {
            // mysql date format (eg. 2003-08-13 or 13-08-2003)
            $year = $regs[1];
            if (99 < $regs[1]) {
                $year = $regs[1];
                if (12 < $regs[2]) {
                    $month = $regs[3];
                    $day = $regs[2];
                } else {
                    $month = $regs[2];
                    $day = $regs[3];
                }
            } else {
                $year = $regs[3];
                if (12 < $regs[2]) {
                    $month = $regs[1];
                    $day = $regs[2];
                } else {
                    $month = $regs[2];
                    $day = $regs[1];
                }
            }
        } elseif (preg_match("/([[:digit:]]{4})([[:digit:]]{2})([[:digit:]]{2})([[:digit:]]{2})([[:digit:]]{2})([[:digit:]]{2})/", $datestr, $regs)) {
            // mysql timestamp format (eg. YYYYMMDDHHIISS)
            $year = $regs[1];
            $month = $regs[2];
            $day = $regs[3];
            $hour = $regs[4];
            $minute = $regs[5];
            $second = $regs[6];
        } elseif (preg_match("/([[:digit:]]+):([[:digit:]]+):([[:digit:]]+)/", $datestr, $regs)) {
            // 24 hour time format (eg. 03:12:15, 4:57:6, 12:00:00)
            $hour = $regs[1];
            $minute = $regs[2];
            $second = $regs[3];
        } elseif (preg_match("/([[:digit:]]+)\/([[:digit:]]+)\/([[:digit:]]+)/", $datestr, $regs)) {
            // human date format (eg. 13/08/2003)
            $year = $regs[3];
            if (12 < $regs[2]) {
                $month = $regs[1];
                $day = $regs[2];
            } else {
                $month = $regs[2];
                $day = $regs[1];
            }
        } elseif (preg_match("/([[:digit:]]+)[-\/[:space:]]([a-z]+)[-\/[:space:]]([[:digit:]]+)/i", $datestr, $regs)) {
            // human date format (eg. 13 August 2003, 13-Aug-2003, 13/Aug/2003)
            $year = $regs[3];
            $day = $regs[1];
            $monthabbrs = array('jan', 'feb', 'mac', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov', 'dec');
            $month = array_search(substr(strtolower($regs[2]), 0, 3), $monthabbrs) + 1;
        }

        // convert two digits year presentation to four digits year based on a most probable rule
        if (100 > $year) {
            if (50 > $year) {
                $year += 2000;
            } elseif (50 <= $year) {
                $year += 1900;
            }
        }

        // check if date is valid
        if (! checkdate($month, $day, $year)) {
            return -1;
        }

        // check if time is valid
        if (0 > $hour || 24 < $hour || 0 > $minute || 60 < $minute || 0 > $second || 60 < $second) {
            return -1;
        }

        if (0 == $mode) {			// unix epoch time value
            $retval = mktime($hour, $minute, $second, $month, $day, $year);
        } elseif (1 == $mode) {	// sql datetime format 'YYYY-MM-DD HH:II:SS'
            $retval = $year.'-'.sprintf('%02d', $month).'-'.sprintf('%02d', $day).' '.sprintf('%02d', $hour).':'.sprintf('%02d', $minute).':'.sprintf('%02d', $second);
        } elseif (2 == $mode) {	// sql datetime format 'YYYY-MM-DD'
            $retval = $year.'-'.sprintf('%02d', $month).'-'.sprintf('%02d', $day);
        } elseif (3 == $mode) {	// sql datetime format 'HH:II:SS'
            $retval = sprintf('%02d', $hour).':'.sprintf('%02d', $minute).':'.sprintf('%02d', $second);
        } elseif (4 == $mode) {	// date time stored in any associative array object
            $retval = array(
                        'year' => $year,
                        'month' => $month,
                        'day' => $day,
                        'hour' => $hour,
                        'minute' => $minute,
                        'second' => $second
                    );
        }
        return $retval;
    }

    /**
    * Copy a file, or recursively copy a folder and its contents
    *
    * @param string $source Source path
    * @param string $dest Destination path
    *
    * @return boolean Returns TRUE on success, FALSE on failure
    * @static
    */
    public static function copyDir($source, $dest)
    {
        // Simple copy for a file
        if (is_file($source)) {
            return copy($source, $dest);
        }

        // Make destination directory
        if (! is_dir($dest)) {
            mkdir($dest);
        }

        // Loop through the folder
        $dir = dir($source);
        while (false !== $entry = $dir->read()) {
            // Skip pointers
            if ('.' == $entry || '..' == $entry) {
                continue;
            }

            // Deep copy directories
            if ($dest !== $source.'/'.$entry) {
                self::copyDir($source.'/'.$entry, $dest.'/'.$entry);
            }
        }
        // Clean up
        $dir->close();
        return true;
    }

    /**
    * Recursively remove directory that's not empty as well.
    *
    * @param string $dir directory to remove
    * @return bool Indicates whether successful or otherwise
    * @static
    */
    public static function removeDir($dir)
    {
        // prevent from deleting everything under the very root directory '/'
        if ('' == $dir || '/' == $dir || '\\' == $dir) {
            return false;
        }
        $dh = opendir($dir);
        while (false !== ($file = readdir($dh))) {
            if ('.' != $file && '..' != $file) {
                $path = $dir.'/'.$file;
                if (is_dir($path)) {
                    self::removeDir($path);
                } else {
                    unlink($path);
                }
            }
        }
        closedir($dh);
        return rmdir($dir);
    }

    /**
    * Recursively remove ftp directory that's not empty as well.
    *
    * @param handle $conn_id ftp connection handle
    * @param string $dir directory to remove
    * @return bool
    * @static
    */
    public static function ftpRemoveDir($conn_id, $dir)
    {
        if ('' == $dir || '/' == $dir || '\\' == $dir || '.' == $dir || '..' == $dir) {
            return false;
        }
        $root = $dir; 						// tmp variable
        if (@! ftp_chdir($conn_id, $root)) { 	// enter the DIRECTORY $root
            // echo 'Directory not found.';
            return false;
        }

        if (! empty($dir) && 0 != ($file_list=ftp_nlist($conn_id, '.')) && null !== $file_list) {
            // list content inside the DIRECTORY $root
            foreach ($file_list as $dir) {
                if (ftp_size($conn_id, $dir)== -1) { 					// dirname
                    if (! self::ftpRemoveDir($conn_id, $dir)) { 			// recursion
                        return false;
                    }
                } else {
                    ftp_delete($conn_id, $dir); 						// del file
                }
            }
        }
        ftp_chdir($conn_id, '../'); 		// dir is empty now, move one level up
        if (ftp_rmdir($conn_id, $root)) {	// remove the empty dir
            return true;
        }
        return false;
    }

    /**
    * Check if a string contains numbers/float and/or -ve sign only
    *
    * @param string $str
    * @return bool
    * @static
    */
    public static function isNumberOnly($str)
    {
        return preg_match('/^[-0-9.]+$/', $str);
    }

    /**
    * Check if a string contains letters only
    *
    * @param string $str
    * @return bool
    * @static
    */
    public static function isLetterOnly($str)
    {
        return preg_match('/^[a-z]+$/i', $str);
    }

    /**
    * Check if a string contains alpha numeric characters only
    *
    * @param string $str
    * @return bool
    * @static
    */
    public static function isAlphaNumOnly($str)
    {
        return preg_match('/^[a-z0-9]+$/i', $str);
    }

    /**
    * Check if a string is a valid username which contains only alphanumeric and underscore characters and must only start with a letter
    *
    * @param string $str
    * @return bool
    * @static
    */
    public static function isValidUsername($str)
    {
        return preg_match('/^[a-z0-9][a-z0-9_]+$/i', $str);
    }

    /**
    * Similar to built-in PHP substr() but truncates by the word not longer than the length specified
    *
    * @param string $str
    * @param integer $start
    * @param integer $length
    * @return bool
    * @static
    */
    public static function substr($str, $start = 0, $length = 1024)
    {
        $buf = '';
        if (preg_match("/(.*)[ \t\n\r]([^ \t\n\r]+)$/", substr($str, $start, $length), $regs)) {
            $buf = $regs[1];
        }
        if (3 >= strlen($buf)) {
            $str = substr($str, $start, $length);
        } else {
            $str = $buf;
        }
        return $str;
    }

    /**
    * Similar to built-in PHP trim() but also perform on array of strings by recursively trimming the values contain within the array
    *
    * @param mixed $var can be array or string
    * @return mixed
    * @static
    */
    public static function trim(&$var)
    {
        if (is_array($var)) {
            reset($var);
            while (list($mkey, $mval) = each($var)) {
                if (is_array($mval)) {
                    self::trim($mval);
                    $var[$mkey] = $mval;
                } else {
                    $var[$mkey] = trim($mval);
                }
            }
            reset($var);
        } else {
            $var = trim($var);
        }
        return $var;
    }

    /**
    * Similar to built-in PHP stripslashes() but also perform on array of strings by recursively stripslashes-ing the values contain within the array
    *
    * @param mixed $var can be array or string
    * @return mixed
    * @static
    */
    public static function stripslashes(&$var)
    {
        if (is_array($var)) {
            reset($var);
            while (list($key, $val) = each($var)) {
                if (is_array($val)) {
                    self::stripslashes($val);
                    $var[$key] = $val;
                } else {
                    $var[$key] = stripslashes($val);
                }
            }
            reset($var);
        } else {
            $var = stripslashes($var);
        }
        return $var;
    }

    /**
    * Strips all non-alphanumeric characters from the source string
    *
    * @param string $str source string
    * @return string
    * @static
    */
    public static function stripNonAlphaNum(&$str)
    {
        if (0 == strlen($str)) {
            return false;
        }
        //return strtolower(eregi_replace("(^[0-9]+)|([-!\)\(#$%&\'*+\\./=?^_`{|}~ ]+)", "", trim($str)));
        return preg_replace('/[^a-z0-9_]/i', '', trim($str));
    }

    /**
    * Generates lowercase alphanumeric string by stripping illegal characters from the source string to be used as code
    *
    * @param string $str source string
    * @param integer $len length of the code
    * @return string
    * @static
    */
    public static function getCode($str, $len = 0)
    {
        if (0 == strlen($str)) {
            return false;
        }

        $str = strtolower(self::stripNonAlphaNum($str));
        if (0 < $len) {
            $str = substr($str, 0, $len);
        }
        return $str;
    }

    /**
    * Perform wild card ('*' and '?') masking on a source string.
    * examples :
    *     if (Common::wildcardcmp('myfile.txt','*.txt')) {...} // this is true
    *     if (Common::wildcardcmp('file001.jpg','file??.jpg')) {...} // this is false
    *
    * @param string $str source string
    * @param string $mask
    * @param boolean $case_sensitive
    * @return boolean
    * @static
    */
    public static function wildcardCmp($str, $mask, $caseSensitive = true)
    {
        static $in = array('.', '^', '$', '{', '}', '(', ')', '[', ']', '+', '*', '?', '|', '/');
        static $out = array('\\.', '\\^', '\\$', '\\{', '\\}', '\\(', '\\)', '\\[', '\\]', '\\+', '.*', '.', '\\|', '\\/');
        $mask = '^'.str_replace($in, $out, $mask).'$';
        return preg_replace('/'.$mask.'/'.(($caseSensitive)?'':'i'), $str);
    }

    /**
    * Perform wild card ('*' and '?') masking on a source string.
    * This function is case insensitive.
    * examples :
    *     if (Common::wildcardcmp('myfile.txt','*.txt')) {...} // this is true
    *     if (Common::wildcardcmp('file001.jpg','file??.jpg')) {...} // this is false
    *
    * @param string $str source string
    * @param string $mask
    * @return boolean
    * @static
    */
    public static function wildcardCmpi($str, $mask)
    {
        return self::wildcardCmp($str, $mask, false);
    }

    /**
    * Returns size of file in a proper formatted display (eg. 23.41k, 4.5GB) given the filename and path
    *
    * @param string $file
    * @return string
    * @static
    */
    public static function getFileSize($file)
    {
        $size = filesize($file);
        return self::sizeFormat($size);
    }

    /**
    * Returns a properly formatted size value (eg. 23.41k, 4.5GB) given the value in bytes
    *
    * @param integer $size in bytes
    * @param integer $precision the precision of the floating number to maintain
    * @return string
    * @static
    */
    public static function sizeFormat($size, $precision = 1)
    {
        $unit = '';
        if (1073741824 <= $size) {
            $size = round($size / 1073741824 * 100) / 100;
            $unit = 'GB';
        } elseif (1048576 <= $size) {
            $size = round($size / 1048576 * 100) / 100;
            $unit = 'MB';
        } elseif (1024 <= $size) {
            $size = round($size / 1024 * 100) / 100;
            $unit = 'KB';
        } elseif (0 < $size) {
            $size = $size;
            $unit = 'B';
        }
        return number_format($size, $precision).$unit;
    }

    /**
    * Returns a random password based on alphanumeric character sets with length specified by $len
    *
    * @param integer $len length of password
    * @param array $excludeChars the list of characters to exclude from the string generation
    *
    * @return string
    * @static
    */
    public static function getRandomPassword($len = 9, $excludeChars = array())
    {
        $str = '';
        mt_srand((double)microtime()*1000000);
        //for ($i=0; $i < $len; $i++) {
        while (strlen($str) < $len) {
            $entry = mt_rand(0, 2) % 3;
            if (0 == $entry) {
                $char = chr(mt_rand(65, 90));
            } elseif (1 == $entry) {
                $char = chr(mt_rand(48, 57));
            } else {
                $char = chr(mt_rand(97, 122));
            }
            if (! in_array($char, $excludeChars)) {
                $str .= $char;
            }
        }
        return $str;
    }

    /**
    * Get the system temporary directory name
    *
    * @return string Temporary directory path string that always ends with '/'
    * @static
    */
    public static function getTempDir()
    {
        if (0 < strlen(getenv('TMP'))) {
            $tmpdir = getenv('TMP');
        } elseif (0 < strlen(getenv('TEMP'))) {
            $tmpdir = getenv('TEMP');
        } elseif (0 < strlen(getenv('TMPDIR'))) {
            $tmpdir = getenv('TMPDIR');
        } else {
            $tmpdir = '/tmp';
        }
        $tmpdir = str_replace("\\", '/', $tmpdir);
        if (! preg_match('/\/$/', $tmpdir)) {
            $tmpdir .= '/';
        }
        return $tmpdir;
    }

    /**
    * Check if the time string given in the format of 'HH:MM[:SS]' is a valid time value
    *
    * @return boolean True if it is valid. Otherwise false.
    * @static
    */
    public static function checkTime($timeStr)
    {
        if (preg_match('/^([0-9]{1,2}):([0-9]{1,2})[:]?([0-9]{1,2})?$/', $timeStr, $regs)) {
            $hour = intval($regs[1]);
            $minute = intval($regs[2]);
            $second = intval($regs[3]);
            if (0 > $hour || 23 < $hour) {
                return false;
            }
            if (0 > $minute || 59 < $minute) {
                return false;
            }
            if (0 > $second || 59 < $second) {
                return false;
            }
            return true;
        }
        return false;
    }

    /**
    * Get the system object string by the object type given
    *
    * Requires constant.inc.php, guiconst.inc.php, gui/guibase.inc.php and one of the resource language file (default: resource/language/en_us.utf-8.inc.php)
    *
    * @param integer $objectType One of the MX_OBJECT_* value
    *
    * @return string The corresponding string from the GUI text definition
    * @static
    */
    public static function getObjectString($objectType)
    {
        return mxGUIBase::getText(self::$objectGUIStringMap[$objectType]);
    }

    /**
    * Gets an array of the content type string that are supported -- used in the theme rule
    *
    *
    * @return Array of the content type strings supported.
    * @static
    */
    public static function getContentTypes()
    {
        $contenttype = array( 'text/html',
                              'text/plain',
                              'text/vnd.wap.wml',
                              'text/vnd.wap.wmlscript',
                              'text/xml');
        return $contenttype;
    }

    /**
    * This is a utility function to format a date string according for display purposes.
    *
    * @param \Datetime An \Datetime object to be used for formatting.
    * @return string   The formatted string
    * @static
    */
    public static function getDateString($data)
    {
        $string = mxGUIBase::getText(MX_GUI_NEVER);
        if ($data instanceof \Datetime) {
            if (1990 < $data->getYear()) {
                $string = $data->format(mxGUIBase::getText(MX_GUI_DATE_FORMAT));
            }
        }
        return $string;
    }

    /**
    * This is a utility function to format a date time string according for display purposes.
    *
    * @param \Datetime An \Datetime object to be used for formatting.
    * @return string   The formatted string
    * @static
    */
    public static function getDateTimeString($data)
    {
        $string = mxGUIBase::getText(MX_GUI_NEVER);
        if ($data instanceof \Datetime) {
            if (1990 < $data->getYear()) {
                $string = $data->format(mxGUIBase::getText(MX_GUI_DATETIME_FORMAT));
            }
        }
        return $string;
    }

    /**
    * Get the current UTC time regardless of the timezone the current server time is set to
    *
    * @return integer The unix timestamp since 00:00:00 1 Jan 1970 GMT
    * @static
    */
    public static function getCurrentUTCTime()
    {
        return mktime() - date('Z');
    }

    /**
    * Check if the current server is running on MS Windows OS
    *
    * @return boolean True if it is. Otherwise false
    * @static
    */
    public static function isWindow()
    {
        if (isset($_SERVER['WINDIR']) || stristr($_ENV['OS'], 'windows')) {
            return true;
        }
        return false;
    }

    /**
    *  Returns the phpinfo() output
    *
    *  @param integer $what
    *	Name (constant)   Value Description
    *	INFO_GENERAL 		1 	The configuration line, php.ini location, build date, Web Server, System and more.
    *	INFO_CREDITS 		2 	PHP 4 Credits. See also phpcredits().
    *	INFO_CONFIGURATION 	4 	Current Local and Master values for php directives. See also ini_get().
    *	INFO_MODULES 		8 	Loaded modules and their respective settings. See also get_loaded_modules().
    *	INFO_ENVIRONMENT 	16 	Environment Variable information that's also available in $_ENV.
    *	INFO_VARIABLES 		32 	Shows all predefined variables from EGPCS (Environment, GET, POST, Cookie, Server).
    *	INFO_LICENSE 		64 	PHP License information. See also the » license faq.
    *	INFO_ALL 			-1 	Shows all of the above. This is the default value.
    *
    *
    *  @return string phpinfo() output in HTML
    * @static
    */
    public static function getPHPInfo($what = -1)
    {
        \Snap\App::getInstance()->log('Calling function to get PHP configuration information', SNAP_LOG_WARN);
        ob_start();
        phpinfo($what);
        $data = ob_get_contents();
        ob_end_clean();
        return $data;
    }

    /**
    * Get the current base URL (http[s]://HTTP_HOST/SCRIPT_NAME) that's use to access this page
    *
    * @param boolean $bCompat If true, it will only return URL of such format HTTP_HOST/ + dirname(SCRIPT_NAME) (with no trailing '/' and no scheme)
    *
    * @static
    * @return string The complete URL construct that is used to accessed this page (excluding query string)
    */
    public static function getBaseURL($bCompat = false)
    {
        $scheme = 'http';					// default scheme is http
        //$host = $_SERVER['HTTP_HOST'];
        $port = '';
        if ('on' == $_SERVER['HTTPS']) {	// https (SSL)
            $scheme = 'https';
            if (443 != $_SERVER['SERVER_PORT']) {
                $port = ':'.$_SERVER['SERVER_PORT'];
            }
        } else {							// http
            if (80 != $_SERVER['SERVER_PORT']) {
                $port = ':'.$_SERVER['SERVER_PORT'];
            }
        }
        if ($bCompat) {
            // $str = $_SERVER['HTTP_HOST'].$port.str_replace('//', '/', dirname($_SERVER['SCRIPT_NAME']));
            $str = $_SERVER['HTTP_HOST'].str_replace('//', '/', dirname($_SERVER['SCRIPT_NAME']));
            return preg_replace('/[\/]+$/', '/', $str);
        }
        return $scheme.'://'.$_SERVER['HTTP_HOST'].$port.$_SERVER['SCRIPT_NAME'];
    }

    /**
    * Get the actual full URL used to access this page
    *
    * @static
    * @return string The complete URL construct that is used to access this page (together with query string)
    */
    public static function getFullURL()
    {
        return self::getBaseURL().((0 < strlen($_SERVER['QUERY_STRING']))?'?'.$_SERVER['QUERY_STRING']:'');
    }

    /**
    * Get independent HTML page from a website where all relative links is coverted to absolute link
    *
    * @static
    * @param string $link The URL link pointing to the HTML to grab
    *
    * @return string The complete independent HTML page
    */
    public static function getIndependentHTMLPage($link)
    {
        $page = '';
        if ($fp = fopen($link, 'r')) {
            $url = parse_url($link);
            $host = $url['host'];
            $scheme = $url['scheme'];
            $dirname = dirname($url['path']);
            $dirname = preg_replace('/\/$/', '', $dirname);
            while ($buf = fgets($fp, 4096)) {
                if (stristr($buf, '<body')) {
                    $buf = str_replace('<body', '<xbody', $buf);
                }
                if (preg_match('/href="([^"]+)"/i', $buf, $regs)) {
                    if (preg_match('/^[^http:]/i', $regs[1])) {
                        $buf = preg_replace('/href="([^"]+)"/i', "href=\"".$scheme."://".$host.(('/' == $regs[1][0])?'':$dirname.'/')."\\1\"", $buf);
                    }
                }
                if (preg_match('/src="([^"]+)"/i', $buf, $regs)) {
                    if (preg_match('/^[^http:]/i', $regs[1])) {
                        $buf = preg_replace('/src="([^"]+)"/i', "src=\"".$scheme."://".$host.(('/' == $regs[1][0])?'':$dirname.'/')."\\1\"", $buf);
                    }
                }
                if (preg_match('/background="([^"]+)\.(gif|png|jpg|jpeg)"/i', $buf, $regs)) {
                    if (preg_match('/^[^http:]/i', $regs[1])) {
                        $buf = preg_replace('/background="([^"]+)\.(gif|png|jpg|jpeg)"/i', "background=\"".$scheme."://".$host.(('/' == $regs[1][0])?'':$dirname.'/')."\\1\"", $buf);
                    }
                }
                $page .= $buf;
            }
            fclose($fp);
        }
        return $page;
    }

    /**
    *  This method is used to remove the annoying unreferenced argument warning generated by
    *  PHP.  Use this function to remove any unwanted messages / warnings.
    *
    *  @static
    *  @param mixed
    *  @return void
    */
    public static function UNREFERENCED_ARGUMENT(/* .... */)
    {
        //dummy function to do nothing.....
    }

    /**
    * Get network device info
    *
    * Example of the information on the returned array:
    * array(
    * 	'DEVICE' => 'eth0',
    * 	'BOOTPROTO' => 'none',
    * 	'HWADDR' => '00:0c:29:f0:ec:e9',
    * 	'ONBOOT' => 'yes',
    * 	'TYPE' => 'Ethernet',
    * 	'NETMASK' => '255.255.255.0',
    * 	'IPADDR' => '192.168.2.97',
    * 	'USERCTL' => 'no',
    * 	'PEERDNS' => 'yes',
    * 	'GATEWAY' => '192.168.2.1',
    * 	'IPV6INIT' => 'no'
    * )
    *
    * @static
    *
    * @param string $device The name of the device (eg. 'eth0', 'lo', 'eth1', etc)
    * @param string $linuxFlavor The flavor of the linux distribution (eg. 'redhat', 'fedoracore' or 'suse')
    *
    * @return array The information on the device
    */
    public static function getNetworkDeviceInfo($device = 'eth0')
    {
        // default path
        $info = null;
        $linuxFlavor = Common::getLinuxFlavor();
        $deviceFile = 'ifcfg-'.$device;
        $path = '/etc/sysconfig/network-scripts/';
        if ('redhat' == $linuxFlavor) {
            // same as default path and deviceFile, no need to change path and deviceFile
        } elseif ('suse' == $linuxFlavor) {
            $path = '/etc/sysconfig/network/';
            exec('/sbin/ifconfig '.escapeshellarg($device), $output);
            foreach ($output as $line) {
                if (preg_match('/HWaddr [a-z0-9:]+/i', $line, $regs)) {
                    $deviceFile = 'ifcfg-eth-id-'.strtolower($regs[1]);
                    break;
                }
            }
        }
        $devicePathFile = $path.$deviceFile;
        if (file_exists($devicePathFile)) {
            $info = array();
            $lines = file($devicePathFile);
            foreach ($lines as $line) {
                $line = trim($line);
                if (preg_match('/^([-a-z0-9_]+)=[\']?(.*)[\']?$/i', $line, $regs)) {
                    $info[trim($regs[1])] = trim($regs[2]);
                }
            }
        }
        return $info;
    }

    /**
    * Check whether the current system is running on Solaris OS (SunOS)
    *
    * @static
    *
    * @return boolean True if it is. Otherwise false
    */
    public static function isSolaris()
    {
        $unameInfo = posix_uname();
        if ('SunOS' == $unameInfo['sysname']) {
            return true;
        }
        return false;
    }

    /**
    * Get the flavor of the linux distribution
    *
    * Currently supports two linux flavor: redhat (including fedora core) and suse
    *
    * @static
    *
    * @return string Currently can return either 'redhat', 'suse' or null (for unsupported flavors)
    */
    public static function getLinuxFlavor()
    {
        // supported linux flavors
        if (file_exists('/etc/redhat-release')) {
            return 'redhat';
        } elseif (file_exists('/etc/SuSE-release')) {
            return 'suse';
        }
        return null;
    }

    /**
    * Define method for ETA template mapping
    *
    * @param string $name The name of the variable
    * @param string $value The value of the variable
    *
    * @static
    *
    * @return void
    */
    public static function define($name, $value)
    {
        $GLOBALS['_eta_'][$name] = $value;
    }

    /**
     * Perform the same function as the built-in PHP function htmlspecialchars() except
     * that it will not translate multibyte html entities
     *
     * @param $str
     *
     * @static
     * @return string
     */
    public static function htmlspecialchars_ex($str)
    {
        return preg_replace('/&amp;#([0-9]+);/', '&#$1;', htmlspecialchars($str));
    }

    /**
     * Parse a CSV formatted line of string into arrays
     *
     * @param $str
     * @param $separator The CSV field separator. Default is ",".
     *
     * @static
     * @return array
     */
    public static function getCSVValues($string, $separator=",")
    {
        $elements = explode($separator, $string);
        for ($i = 0; $i < count($elements); $i++) {
            $nquotes = substr_count($elements[$i], '"');
            if (1 == $nquotes%2) {
                for ($j = $i+1; $j < count($elements); $j++) {
                    if (0 < substr_count($elements[$j], '"')) {
                        // Put the quoted string's pieces back together again
                        array_splice(
                           $elements,
                           $i,
                           $j-$i+1,
                           implode($separator, array_slice($elements, $i, $j-$i+1))
                       );
                        break;
                    }
                }
            }
            if (0 < $nquotes) {
                // Remove first and last quotes, then merge pairs of quotes
                $qstr =& $elements[$i];
                $qstr = substr_replace($qstr, '', strpos($qstr, '"'), 1);
                $qstr = substr_replace($qstr, '', strrpos($qstr, '"'), 1);
                $qstr = str_replace('""', '"', $qstr);
            }
        }
        return $elements;
    }

    /**
    * Format a number (eg. rate) based on 6 digit precision
    *
    * @param $num The number to format
    * @param $thousand_separator The character to use as the thousand_separator. Default is ','
    *
    * @return The formatted number in string
    * @static
    */
    public static function rate_format($num, $thousand_separator = ',')
    {
        if (100000 <= $num) {
            $decpoint = 0;
        } elseif (10000 <= $num) {
            $decpoint = 1;
        } elseif (1000 <= $num) {
            $decpoint = 2;
        } elseif (100 <= $num) {
            $decpoint = 3;
        } elseif (10 <= $num) {
            $decpoint = 4;
        } else {
            $decpoint = 5;
        }
        return number_format($num, $decpoint, '.', $thousand_separator);
    }

    /**
    * Resize an image. Proportions will be maintained.
    *
    * The size of the smaller value between $tn_width and $tn_height will be used to determine the overall proportion.
    *
    * @param mixed $data The image data to resize
    * @param integer $tn_width The new width to resize to (in pixels)
    * @param integer $tn_height The new height to resize to (in pixels)
    * @param string $type The image type
    *
    * @return	mixed The newly resized image data on success. Otherwise null
    */
    public static function doResizeImage($data, $tn_width, $tn_height, $type)
    {
        $resizedData = null;
        $tn_width = intval($tn_width);
        $tn_height = intval($tn_height);
        if (0 < $tn_width || 0 < $tn_height) {
            if (extension_loaded('imagick')) {
                //$this->log('imagick extension found!', MX_LOG_DEBUG);
                $handle = new Imagick();
                $handle->readImageBlob($data);
                $height = $handle->getImageHeight();
                $width = $handle->getImageWidth();
                $aspectRatio = $width / $height;
                if (0 < $tn_height && 0 < $tn_width) {
                    if (0 == $tn_height) {
                        $tn_height = $height;
                    }
                    if (0 == $tn_width) {
                        $tn_width = $width;
                    }
                    if ($tn_height > $height) {
                        $tn_height = $height;
                    }
                    if ($tn_width > $width) {
                        $tn_width = $width;
                    }
                    $newHeight = $tn_width / $aspectRatio;
                    if ($newHeight > $tn_height) {
                        $tn_width = $tn_height * $aspectRatio;
                    } else {
                        $tn_height = $newHeight;
                    }
                }
                //$this->log("Image original size is (W x H): $width x $height, resizing to $tn_width x $tn_height ...", MX_LOG_INFO);
                $handle->thumbnailImage($tn_width, $tn_height);
                $height = $handle->getImageHeight();
                $width = $handle->getImageWidth();
                //$this->log("New image size is (W x H): $width x $height", MX_LOG_INFO);
                $resizedData = $handle->getImageBlob();
            } else {
                //$this->log('imagick extension not found! Using gd2 extension', MX_LOG_WARNING);
                require_once('resizeimage.inc.php');
                $ri = new mxResizeImage;
                $ri->loadFromBlob($data, $type);
                $ri->thumbnail($tn_width, $tn_height);
                $resizedData = $ri->getBlob();
            }
        }
        return $resizedData;
    }

    /**
    * Check whether the script is running in CLI mode or not
    *
    * @return	boolean True if script is running in CLI mode. Otherwise false.
    */
    public static function isCLI()
    {
        return ('cli' == PHP_SAPI);
    }

    /**
    * Get real remote/client IP currently being used, bypasssing proxies and load balancers
    *
    * @return	boolean True if script is running in CLI mode. Otherwise false.
    */
    public static function getRemoteIP()
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        if (getenv('HTTP_CLIENT_IP') && strcasecmp(getenv('HTTP_CLIENT_IP'), 'unknown')) {
            $ip = getenv('HTTP_CLIENT_IP');
        } elseif (getenv('HTTP_X_FORWARDED_FOR') && strcasecmp(getenv('HTTP_X_FORWARDED_FOR'), 'unknown')) {
            $str = getenv('HTTP_X_FORWARDED_FOR');
            $ips = explode(',', $str);
            $ip = trim($ips[0]);
        //$ip = getenv('HTTP_X_FORWARDED_FOR');
        } elseif (getenv('REMOTE_ADDR') && strcasecmp(getenv('REMOTE_ADDR'), 'unknown')) {
            $ip = getenv('REMOTE_ADDR');
        }
        return $ip;
    }

    public static function numberFormatWithoutRounding($number, $decimal = 2, $decimalPoint = '.', $thousandSeparator = ',')
    {
        list($n, $d) = explode('.', sprintf('%f', $number));
        $number = $n . '.' . substr($d, 0, $decimal);
        return number_format($number, $decimal, $decimalPoint, $thousandSeparator);
    }

    public static function isMobileBrowser()
    {
        //  $useragent=$_SERVER['HTTP_USER_AGENT'];
        // if(preg_match('/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i',$useragent)||preg_match('/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i',substr($useragent,0,4)))
        // 			return true;
        $tablet_browser = 0;
        $mobile_browser = 0;

        if (preg_match('/(tablet|ipad|playbook)|(android(?!.*(mobi|opera mini)))/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $tablet_browser++;
        }

        if (preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|android|iemobile)/i', strtolower($_SERVER['HTTP_USER_AGENT']))) {
            $mobile_browser++;
        }

        if ((0 < strpos(strtolower($_SERVER['HTTP_ACCEPT']), 'application/vnd.wap.xhtml+xml')) or ((isset($_SERVER['HTTP_X_WAP_PROFILE']) or isset($_SERVER['HTTP_PROFILE'])))) {
            $mobile_browser++;
        }

        $mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'], 0, 4));
        $mobile_agents = array(
            'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',
            'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',
            'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',
            'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',
            'newt','noki','palm','pana','pant','phil','play','port','prox',
            'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',
            'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',
            'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',
            'wapr','webc','winw','winw','xda ','xda-');

        if (in_array($mobile_ua, $mobile_agents)) {
            $mobile_browser++;
        }

        if (0 < strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'opera mini')) {
            $mobile_browser++;
            //Check for tablets on opera mini alternative headers
            $stock_ua = strtolower(isset($_SERVER['HTTP_X_OPERAMINI_PHONE_UA'])?$_SERVER['HTTP_X_OPERAMINI_PHONE_UA']:(isset($_SERVER['HTTP_DEVICE_STOCK_UA'])?$_SERVER['HTTP_DEVICE_STOCK_UA']:''));
            if (preg_match('/(tablet|ipad|playbook)|(android(?!.*mobile))/i', $stock_ua)) {
                $tablet_browser++;
            }
        }
        if (0 < $tablet_browser || 0 < $mobile_browser) {
            return true;
        }
        return false;
    }

    /**
    * This is a utility function to convert a UTC format to user timezone format.
    * WayneLee - 14/5/2018
    * @param UTC date object ('2018-05-14 00:35:35')
    * @return User local date object ('2018-05-14 08:35:35')
    * @static
    */
    public static function convertUTCToUserDatetime($date)
    {
        if ($date instanceof \DateTime) {
            $convertedDate = new \Datetime($date->format("Y-m-d H:i:s"), \Snap\App::getInstance()->getserverTimezone());
            $convertedDate->setTimezone(\Snap\App::getInstance()->getUserTimezone());

            return $convertedDate;
        } else {
            return false;
        }
    }

    /**
    * This is a utility function to convert a user timezone format to UTC format.
    * WayneLee - 14/5/2018
    * @param User local date object ('2018-05-14 08:35:35')
    * @return UTC date object('2018-05-14 00:35:35')
    * @static
    */
    public static function convertUserDatetimeToUTC($date)
    {
        if ($date instanceof \DateTime) {
            $convertedDate = new \DateTime($date->format("Y-m-d\TH:i:s"), \Snap\App::getInstance()->getUserTimezone());
            $convertedDate->setTimezone(\Snap\App::getInstance()->getserverTimezone());

            return $convertedDate;
        } else {
            return false;
        }
    }

    /**
     * @param string    $filePath   Path to spreadsheet
     * @param boolean   $readOnly   Read only mode
     * @param string    $callback   Name of callback function
     * 
     * @method mixed callback($worksheetNum, $rowNum, array $rowdata, PhpSpreadsheet\Row $row) Callback prototype
     * 
     * @throws \Snap\InputException   File is not found on $filePath
     * @throws \Snap\InputException   Unable to find callback function
     * @throws \PhpOffice\PhpSpreadsheet\Reader\Exception   Unable to match PHPSpreadsheet reader to file
     * 
     * @return array $worksheetData 
     */
    public static function iterateSpreadsheet(string $filePath, $callback, $readOnly = true) 
    {
        if ( !file_exists($filePath)) {
            throw new InputException("Unable to find file given", InputException::GENERAL_ERROR);
        }

        try {
            $reader = \PhpOffice\PhpSpreadsheet\IOFactory::createReaderForFile($filePath);
            $reader->setReadDataOnly($readOnly);
            $spreadsheet = $reader->load($filePath);

            $worksheetNum = 1;
            $returnData = [];
            // iterate each worksheet
            foreach ($spreadsheet->getWorksheetIterator() as $worksheet) {
                $rowNum = 1;
                $returnData[$worksheetNum] = [];

                // iterate each row
                foreach ($worksheet->getRowIterator() as $row) {
                    
                    // get the data within the row
                    $rowData = [];
                    foreach ($row->getCellIterator() as $col) {
                        $rowData[$col->getCoordinate()] = $col->getValue();
                    }

                    $returnData[$worksheetNum][] = call_user_func_array($callback, [$worksheetNum, $rowNum, $rowData, $row]);
                    $rowNum++;
                }

                $worksheetNum++;
            }
        } catch (\Exception $e) {
            // force cleanup of resources
            unset($spreadsheet);
            unset($reader);           
            throw $e; // rethrow exception
        }


        unset($spreadsheet);
        unset($reader);

        return $returnData;
    }

}
?>
