<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap;

Use Snap\crypt;

/**
* Encapsulate the operations and manipulations on configuration file
*
* The config file format have to be in:
*		name = some value
*		name234 = "some value"
*		name_234 = "some "value""
* name can only contain alphanumeric and underscore characters
*
* @author   Ivan Hoo <ivan@silverstream.my>
* @version  1.0
* @package  snap.base
*/
class Config
{
    private $encryptionKey = '!@^f_1VaN;OF#_+9D3V0N3ty';

    /**
    * Singleton instance
    *
    * @var      Config
    */
    protected static $instance = null;

    /**
    * The config file name and path
    *
    * @var		string
    */
    private $file = '';

    /**
    * The loaeded config file name and path
    *
    * @var		string
    */
    private $loadedFile = '';


    /**
    * The entire config file content in a string
    *
    * @var		string
    */
    private $content = '';

    /**
    * Contains the array of configuration data
    *
    * @var		array
    */
    private $config = array();

    /**
    * Contain comments for a particular config name
    *
    * @var		array
    */
    private $comments = array();

    private $app = null;

    /*
    * Constructor function
    *
    * @param string $file The config file name and path
    *
    * @access public
    */
    public function __construct($file = '')
    {
        $file = trim($file);
        $this->set($file);
    }

    /**
    * Check if the file pointed by $file exists
    *
    * @param  string $file The file name and path
    *
    * @return boolean True if exists. Otherwise false.
    */
    public function isExists($file)
    {
        if (file_exists($file)) {
            return true;
        }
        return false;
    }

    /**
    * Set the file for this class to load
    *
    * @param  string $file The file name and path
    *
    * @return boolean True if successful. Otherwise false.
    */
    public function set($file)
    {
        if ($this->isExists($file)) {
            $this->file = $file;
            return true;
        }
        $this->file = '';
        return false;
    }

    /**
    * Check whether the current file has been loaded and processed before
    *
    * @return boolean True if successful. Otherwise false.
    */
    public function isLoaded()
    {
        if ($this->file == $this->loadedFile) {
            return true;
        }
        return false;
    }

    /**
    * Load the configuration file into the class members from cache
    *
    * @return boolean True if successful. Otherwise false.
    */
    public function loadFromCache()
    {
        if ($this->isLoaded()) {
            return true;
        }
        if (! Common::isCLI() && function_exists('xcache_get')) {
            $data = xcache_get($this->file);
            if (null !== $data) {
                $this->config = unserialize($data);
                $this->loadedFile = $this->file;
                return true;
            }
        }
        return false;
    }

    /**
    * Load the configuration file into the class members
    *
    * @param boolean $append Whether to append the newly loaded configuration into existing configuration (Default: false)
    * @param boolean $cache Whether to cache the newly loaded configuration (Default: true)
    *
    * @return boolean True if successful. Otherwise false.
    */
    public function load($append = false, $cache = true)
    {
        $this->loadedFile = '';
        $fp = fopen($this->file, 'r');
        if (! $fp) {
            // unable to read file (file does not exists or no permission to read the file)
            return false;
        }
        if (! $append) {
            $this->config = array();
        }
        $key = '';
        $val = '';
        $comment = '';
        $num = 0;
        while (! feof($fp)) {
            $buf = fgets($fp, 4096);
            $this->content .= $buf;
            $buf = trim($buf);
            $num++;
            if (empty($buf)) {
                $comment = '';
                continue;
            }
            if (preg_match('/^#(.*)$/', $buf, $regs)) {
                if (0 < strlen($comment)) {
                    $comment .= "\n#";
                }
                $comment .= $regs[1];  //trim($regs[1]);
            } elseif (preg_match("/^([a-z0-9_.<>()]+)(\[\])?([ |\t]+)?=(.*)$/i", $buf, $regs)) {
                $key = strtolower(trim($regs[1]));
                $val = trim($regs[4]);
                if (preg_match('/^"/', $val)) {
                    if (preg_match('/"$/', $val)) {
                        $val = preg_replace('/^"/', '', $val);
                        $val = preg_replace('/"$/', '', $val);
                    }
                }
                $val = trim($val);
                if ('[]' == $regs[2]) {
                    $this->config[$key][] = $val;
                } else {
                    $this->config[$key] = $val;
                }
                if (0 < strlen($comment)) {
                    $this->comments[$key] = $comment;
                    $comment = '';
                }
            }
        }
        fclose($fp);
        $this->loadedFile = $this->file;
        if ($cache && ! Common::isCLI() && function_exists('xcache_set')) {
            xcache_set($this->file, serialize($this->config));
        }
        return true;
    }

    /**
    * Get the entire config file content in a string
    *
    * @return string
    */
    public function getContent()
    {
        return $this->content;
    }

    /**
    * Save the entire string as the config file content
    *
    * @return boolean True if successful. Otherwise false.
    */
    public function saveContent($content)
    {
        $fp = fopen($this->file, 'w');
        if ($fp) {
            fwrite($fp, $content, strlen($content));
            fclose($fp);
            return true;
        }
        return false;
    }

    /**
    * Dump all the config data onto screen
    *
    * @return void
    */
    public function dumpConfigs()
    {
        echo "<pre>\n";
        echo "</pre>\n";
    }

    /**
    * Save the configuration data from class to file
    *
    * @return boolean True if successful. Otherwise false.
    */
    public function save()
    {
        return $this->saveAs($this->file);
    }

    /**
    * Save the configuration data from class to filename given by $file
    *
    * @param  string $file The file name and path
    *
    * @return boolean True if successful. Otherwise false.
    */
    public function saveAs($file)
    {
        $fp = fopen($file, 'w');
        if (! $fp) {
            // no permission to create or failed to overwrite an existing file
            return false;
        }
        fputs($fp, $this->getHeader());
        fputs($fp, "\n");
        //ksort($this->config);
        foreach ($this->config as $key => $val) {
            if (0 < strlen($this->comments[$key])) {
                fputs($fp, "\n#".$this->comments[$key]."\n");
            }
            if (is_array($val)) {
                foreach ($val as $val2) {
                    fputs($fp, strtolower($key).'[] = '.$val2."\n");
                }
            } else {
                fputs($fp, strtolower($key).' = '.$val."\n");
            }
        }
        fclose($fp);
        return true;
    }

    /**
    * Include comment for a particular name configuration
    *
    * @param  string $file The config key name
    * @param  string $comment The comment message
    *
    * @return void
    */
    public function setComment($nm, $comment)
    {
        $nm = strtolower($nm);
        $comment = str_replace("\n", '', $comment);
        $comment = str_replace("\r", '', $comment);
        $this->comments[$nm] = trim($comment);
    }

    /**
    * Get the filename path
    *
    * @return string The filename path
    */
    public function getFilename()
    {
        return $this->file;
    }

    /**
    * Add (also overwrite) a config key
    *
    * @param string $nm A config key to add
    * @param string $val A value corresponding to the config key
    * @param string $comment Comment to explain abt the config key
    *
    * @return boolean True if successful. False if $nm is invalid.
    */
    public function add($nm, $val, $comment = '')
    {
        $nm = strtolower($nm);
        if (! preg_match('/^[a-z0-9_]+$/', $nm)) {
            return false;
        }
        $this->config[$nm] = trim($val);
        $this->comments[$nm] = trim($comment);
        return true;
    }

    /**
    * Remove a config key
    *
    * @param string $nm A config key to remove
    *
    * @return void
    */
    public function del($nm)
    {
        $nm = strtolower($nm);
        unset($this->config[$nm]);
        unset($this->comments[$nm]);
    }

    /**
    * Check if a config key exists
    *
    * @param string $nm A config key to check
    *
    * @return boolean True if exists. Otherwise false.
    */
    public function isKeyExists($nm)
    {
        return isset($this->config[strtolower($nm)]);
    }

    /**
    * Get the header messages for the config file
    *
    * @return string The header messages
    */
    public function getHeader()
    {
        $str = "######################################################################\n";
        $str .= "##\n";
        $str .= "##  Copyright Silverstream Technology Sdn Bhd.\n";
        $str .= "##  All Rights Reserved (c) 2016, 2017\n";
        $str .= "##\n";
        $str .= "######################################################################\n";
        return $str;
    }

    /**
    * Overloaded PHP built-in getter
    *
    * @param mixed $nm
    *
    * @return   mixed
    */
    public function __get($nm)
    {
        $dataArr = Array();
        $key = strtolower($nm);
        //Added functionality by devon on 2009/02/22 to support encryption string in the config file for secure transactions.
        if (isset($this->config[$key])) {
            if (preg_match('/ENC##(.*)##$/', $this->config[$key], $dataArr)) {
                $crypto = (new Crypt)
                            ->setCipher('tripledes') 	// set the cipher
                            ->setMode('cfb') 		// set encryption mode
                            ->setKey($this->encryptionKey); 			// set key
                $decryptedRaw = trim($crypto->decrypt(base64_decode($dataArr[1])));
                //preg_match( '/^(.*)!!(.*)!!(.*)$/', $decryptedRaw, $fragments);
                $this->config[$key] = $decryptedRaw;  //$fragments[2]; //reset the array so that we do not need to redo the decryption second time.
                return $decryptedRaw;
            } else {
                return $this->config[$key];
            }
        }
        return null;
    }

    /**
    * Overloaded PHP built-in setter
    *
    * @param mixed $nm
    * @param mixed $val
    *
    * @return   mixed
    */
    public function __set($nm, $val)
    {
        $key = strtolower($nm);
        if (isset($this->config[$key])) {
            $this->config[$key] = trim($val);
            return true;
        }
        return false;
    }
}
?>
