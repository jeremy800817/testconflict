<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap;

/**
 *  Name: Crypt Class
 *
 *  Version: 1.1
 *  Date Released: 11/18/02  (Updated by Devon to support PHP 5 on 02/10/2003)
 *  Full revamp by Devon to support PHP7.2 where mcrypt module has been removed (2018/8/1)
 *
 * 	This class will require phpseclib\phpseclib
 *
 *  @author Devon Koh <devon@silverstream.my>
 * @package  snap.base
 *
 */
class Crypt
{
    const DEFAULTMODE = 'cfb'; // default encryption mode to use
    const DEFAULTCIPHER = 'twofish'; // default cipher to use

    /**
    * Supported list of ciphers
    *
    * @var      string
    */
    private $supportedCipher = [ 'twofish' => '\phpseclib\Crypt\Twofish',
                             'tripledes' => '\phpseclib\Crypt\TripleDES',
                             'des' => '\phpseclib\Crypt\DES',
                             'rihndael' => '\phpseclib\Crypt\Rijndael',
                             'rc2'  => '\phpseclib\Crypt\RC2',
                             'rc4'  => '\phpseclib\Crypt\RC4'];

    /**
    * Supported list of ciphers modes
    *
    * @var      string
    */
    private $supportedModes = [
            'cbc' => '\phpseclib\Crypt\Base::MODE_CBC',
            'cfb' => '\phpseclib\Crypt\Base::MODE_CFB8',
            'ctr' => '\phpseclib\Crypt\Base::MODE_CTR',
            'ecb' => '\phpseclib\Crypt\Base::MODE_ECB',
            'ncfb' => '\phpseclib\Crypt\Base::MODE_CFB',
            'nofb' => '\phpseclib\Crypt\Base::MODE_OFB',
            'ofb' => '\phpseclib\Crypt\Base::MODE_OFB8',
            'stream' => '\phpseclib\Crypt\Base::MODE_STREAM'
        ];

    /**
    * cipher to encrypt with
    *
    * cipher to encrypt with
    *
    * @var      string
    */
    private $cipher;

    /**
    * encryption/decription key
    *
    * encryption/decription key
    *
    * @var      string
    */
    private $key;

    private $cipherObj = null;

    /**
    * encryption mode to use
    *
    * encryption mode to use - cfb mode should be used for string while cbc mode used for files.
    *
    * @var      string
    */
    private $mode;

    /**
     * Constructor to quickly initialise the cipher for use.
     *
     * @param String $cipher Valid name of cipher to use.  Usually is 'twofish', 'tripledes' or 'des'
     * @param String $mode   Valid mode to use for cipher
     * @param String $key    The key to use.
     */
    public function __construct($cipher = null, $mode = null, $key = null)
    {
        if ($cipher) {
            $this->setCipher($cipher, $mode, $key);
        }
        return $this;
    }

    /**
    * clears the key so it can't be fetched by get_key later.
    *
    * clears the key so it can't be fetched by get_key later
    *
    */
    public function clearKey()
    {
        $this->key = '';
    }

    /**
    * creates an IV.
    *
    * creates an IV
    *
    */
    public function createIV()
    {
        // before we create an IV make sure cipher is set
        if ((! isset($this->cipher)) || (! isset($this->mode))) {
            $this->log(__CLASS__.'::'.__METHOD__.' - Cipher and mode must be set first', SNAP_LOG_ERROR);
            return 0;
        }

        // open encryption module
        $this->openCipher();
        $size = $this->cipherObj->getBlockLength() / 8;

        $iv = '';
        // try to generate the iv
        for ($i = 0; $i < $size; $i++) {
            $iv .= chr(rand(1, 250));
        }
        // if we couldn't generate the iv display an error
        if (! $iv) {
            $this->log(__CLASS__.'::'.__METHOD__.' - Failed creating IV', SNAP_LOG_ERROR);
        }
        // return iv
        return $iv;
    }

    /**
    * Do decryption.
    *
    * @param string The decrypted string data
    * @param boolean Indicates whether to keep IV or not.
    *
    * @return   string	The decrypted data
    */
    public function decrypt($encrypted, $keepIV = 0)
    {
        if (0 == strlen($encrypted)) {
            return '';
        }  //nothing to decrpyt or encrypt....
        if ((! isset($this->cipher)) || (! isset($this->mode)) || (! isset($this->key))) {
            $this->log(__CLASS__.'::'.__METHOD__.' - Cipher, mode, and key must be set before decrypting', SNAP_LOG_ERROR);
            return '';
        }

        // extract encrypted value from base64 encoded value
        $data = base64_decode($encrypted);

        // open encryption module
        $this->openCipher();
        // get what size the IV should be
        $ivsize = $this->cipherObj->getBlockLength() / 8;
        // get the IV from the encrypted string
        $iv = substr($data, 0, $ivsize);
        $this->cipherObj->setIV($iv);
        $this->cipherObj->setKey($this->key);
        // remove the IV from the data so we decrypt cleanly
        if (1 != $keepIV) {
            $data = substr($data, $ivsize);
        }
        // decrypt the data
        $decrypted = $this->cipherObj->decrypt($data);

        // get rid of original data
        unset($data);

        return $decrypted;
    }

    /**
    * decrypts a file.
    *
    * decrypts a file
    *
    * @param string The encrypted file name
    * @param string The name of the file to place the decrypted contents.
    *
    * @return   integer   The status where 0 means failure while 1 means success.
    */
    public function decryptFile($sourcefile, $destfile)
    {
        // make sure required fields are specified
        if ((! isset($this->cipher)) || (! isset($this->mode)) || (! isset($this->key))) {
            $this->log(__CLASS__.'::'.__METHOD__.' - Cipher, mode, and key must be set before decrypting', SNAP_LOG_ERROR);
            return 0;
        }

        // make sure file exists and is readable
        if (! is_readable($sourcefile)) {
            return 0;
        }

        // touch destion file so it will exist when we check for it
        @touch($destfile);

        if (! is_writable($destfile)) {
            return 0;
        }

        // read the file into memory and encrypt it
        $fp = fopen($sourcefile, "r");

        // return false if unable to open file
        if (! $fp) {
            return 0;
        }

        $filecontents = fread($fp, filesize($sourcefile));
        fclose($fp);

        // open the destionation file for writing
        $dest_fp = fopen($destfile, "w");

        // return false if unable to open file
        if (! $dest_fp) {
            return 0;
        }

        // write decrypted data to file
        fwrite($dest_fp, $this->decrypt($filecontents));

        // close encrypted file pointer
        fclose($dest_fp);

        return 1;
    }

    /**
    * Encrypt data which is normally a string.
    *
    * Encrypt data which is normally a string
    *
    * @param string The contents to be encrypted
    *
    * @return   mixed The encrypted data
    */
    public function encrypt($data)
    {
        if ((! isset($this->cipher)) || (! isset($this->mode)) || (! isset($this->key))) {
            $this->log(__CLASS__.'::'.__METHOD__.' - Cipher, mode, and key must be set before encrypting', SNAP_LOG_ERROR);
            return '';
        }
        $this->openCipher();
        // create an IV
        $iv = $this->createIV();
        $this->cipherObj->setIV($iv);
        $this->cipherObj->setKey($this->key);
        $encrypted_data = $this->cipherObj->encrypt($data);
        // get rid of original data
        unset($data);
        // return base64 encoded string
        return base64_encode($iv . $encrypted_data);
    }

    /**
    * encrypts a file.
    *
    * encrypts a file
    *
    * @param string  The original data that is to be encrypted
    * @param string  File name of the encrypted data.
    *
    * @return   integer   0 if the function fails.  Otherwise returns 1.
    */
    public function encryptFile($sourcefile, $destfile)
    {
        // make sure required fields are specified
        if ((! isset($this->cipher)) || (! isset($this->mode)) || (! isset($this->key))) {
            $this->log(__CLASS__.'::'.__METHOD__.' - Cipher, mode, and key must be set before encrypting', SNAP_LOG_ERROR);
            return 0;
        }

        // make sure file exists and is readable
        if (! is_readable($sourcefile)) {
            return 0;
        }

        // touch destion file so it will exist when we check for it
        @touch($destfile);

        if (! is_writable($destfile)) {
            return 0;
        }

        // read the file into memory and encrypt it
        $fp = fopen($sourcefile, "r");

        // return false if unable to open file
        if (! $fp) {
            return 0;
        }

        $filecontents = fread($fp, filesize($sourcefile));
        fclose($fp);

        // open the destionation file for writing
        $dest_fp = fopen($destfile, "w");

        // return false if unable to open file
        if (! $dest_fp) {
            return 0;
        }

        // write encrypted data to file
        fwrite($dest_fp, $this->encrypt($filecontents));

        // close encrypted file pointer
        fclose($dest_fp);

        return 1;
    }

    /**
    * This function *ATTEMPTS* to generate a secure encryption/decryption key
    *
    * *ATTEMPTS* to generate a secure encryption/decryption key
    *
    * @return   string   The generated keys data
    */
    public function generateKey()
    {
        /* generate an random decryption key */
        $decryptkey = bin2hex(md5(uniqid(rand(), 1)));

        /* get a unique id with a random prefix */
        $value = md5(uniqid(rand(), 1));

        // backup current encryption key
        $oldkey = $this->key;

        // set the encryption/decryption key to the randomly generated decryption key
        $this->setKey($decryptkey);

        // decrypt $value with an invalid decryption key so we get garbage
        $returnkey = $this->decrypt($value, 1);

        // restore encryption key
        $this->key = $oldkey;

        // cleanup variables
        unset($oldkey, $decryptkey);

        // return encryption key, should be base64 encoded for storage
        return $returnkey;
    }

    /**
    * return the name of the current cipher.
    *
    * return the name of the current cipher
    *
    * @return   string Returns the cipher type used.
    */
    public function getCipher()
    {
        return $this->cipher;
    }

    /**
    * return the encryption/decryption key.
    *
    * return the encryption/decryption key
    *
    * @return   string Returns the key used in encryption / decryption
    */
    public function getKey()
    {
        return $this->key;
    }

    /**
    * return the encryption mode.
    *
    * return the encryption mode
    *
    * @return   string   Returns the mode used in cipher.
    */
    public function getMode()
    {
        return $this->mode;
    }

    /**
    * attempt to set the cipher to $ciphername, verifies ciphername against list of supported ciphers.
    *
    * attempt to set the cipher to $ciphername, verifies ciphername against list of supported ciphers
    *
    * @param string The name of the cipher.
    *
    * @return   integer 0 of false.  Otherwise returns 1.
    */
    public function setCipher($ciphername, $mode = null, $key = null)
    {
        $this->cipherObj = null;
        // make sure encryption mode is a valid mode
        if (array_key_exists($ciphername, $this->supportedCipher)) {
            $this->cipher = $ciphername;
        } else {
            throw \Snap\InputException("Selected cipher name $ciphername is not available", \Snap\InputException::GENERAL_ERROR);
        }
        if ($mode) {
            $this->setMode($mode);
        }
        if ($key) {
            $this->setKey($key);
        }
        return $this;
    }

    /**
    * set encryption key.
    *
    * set encryption key
    *
    * @param string The encryption key to encrypt
    *
    * @return   integer  Success = 1 and failure = 0
    */
    public function setKey($encryptkey)
    {
        // make sure cipher and mode are set before setting IV
        if ((! isset($this->cipher)) || (! isset($this->mode))) {
            $this->log(__CLASS__.'::'.__METHOD__.' - Cipher and mode must be set before using setting key', SNAP_LOG_ERROR);
            return 0;
        }

        if (! empty($encryptkey)) {
            $this->openCipher();
            // get the size of the encryption key
            $keysize = $this->cipherObj->getKeyLength() / 8;

            // if the encryption key is less than 32 characters long and the expected keysize is at least 32 md5 the key
            if ((32 > strlen($encryptkey)) && (32 <= $keysize)) {
                $encryptkey = md5($encryptkey);
            // if encryption key is longer than $keysize and the keysize is 32 then md5 the encryption key
            } elseif ((strlen($encryptkey) > $keysize) && (32 == $keysize)) {
                $encryptkey = md5($encryptkey);
            } else {
                if ($keysize > strlen($encryptkey)) {
                    // if encryption key is shorter than the keysize, strpad it with space
                    $encryptkey = str_pad($encryptkey, $keysize);
                } else {
                    // if encryption key is longer than the keysize substr it to the correct keysize length
                    $encryptkey = substr($encryptkey, 0, $keysize);
                }
            }
            //echo "Keysize = $keysize, Key = $encryptkey, Key Length = ".strlen($encryptkey)."<br>\n";
            $this->key = $encryptkey;
        } else {
            throw \Snap\InputException("Unable to set the encryption key because it is empty", \Snap\InputException::GENERAL_ERROR);
        }
        return $this;
    }

    /**
    * attempt to set encryption mode to $encryptmode, verifies mode against list of supported modes.
    *
    * attempt to set encryption mode to $encryptmode, verifies mode against list of supported modes
    *
    * @param string The encryption mode to use. cfb mode should be used for string while cbc mode used for files
    *
    * @return   $this
    */
    public function setMode($encryptmode)
    {
        // make sure cipher and mode are set before setting IV
        if ((! isset($this->cipher))) {
            $this->log(__CLASS__.'::'.__METHOD__.' - Cipher and mode must be set before using setting key', SNAP_LOG_ERROR);
            throw \Snap\InputException("Unable to set mode before setting the cipher", \Snap\InputException::GENERAL_ERROR);
        }

        // make sure encryption mode is a valid mode
        if (array_key_exists($encryptmode, $this->supportedModes)) {
            $this->mode = $encryptmode;
        } else {
            throw \Snap\InputException("Selected mode $encryptmode is not available", \Snap\InputException::GENERAL_ERROR);
        }
        return $this;
    }

    /**
    * Attempt to open cipher, verify cipher was opened otherwise throw an error
    *
    * @return resource Encryption descriptor, or FALSE on error
    */
    private function openCipher()
    {
        if (null == $this->cipherObj) {
            $className = $this->supportedCipher[$this->cipher];
            $modeConst = constant($this->supportedModes[$this->mode]);
            $this->cipherObj = new $className($modeConst);
            $this->cipherObj->setKeyLength(256);
            $this->cipherObj->disablePadding();
        }
        return $this->cipherObj;
    }

    private function log($message, $level = SNAP_LOG_WARNING)
    {
        App::getInstance()->log($message, $level);
    }
}
?>