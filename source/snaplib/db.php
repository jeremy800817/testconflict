<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap;

/**
* This is the wrapper class for the PDO database object so that any customisation can be done here.  All functions
* available in the PDO object can be called directly from here.
*
* @author  Devon Koh <devon@silverstream.my>
* @version 1.0
* @package  snap.base
*/
class Db extends \PDO
{
    /**
    * Constructor function
    *
    */
    public function __construct($type, $host, $username, $password, $database, $defaultCharset = 'utf8', $sslKey = '', $sslCert = '', $sslCa = '')
    {
        try {
            list($host, $port) = explode(':', $host);
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_PERSISTENT => true,
                \PDO::MYSQL_ATTR_INIT_COMMAND => 'SET NAMES \'UTF8\'',
                \PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
                \PDO::MYSQL_ATTR_SSL_VERIFY_SERVER_CERT => false
            ];
            if (0 < strlen($sslKey)) {
                $options[\PDO::MYSQL_ATTR_SSL_KEY] = $sslKey;
            }
			if (0 < strlen($sslCert)) {
                $options[\PDO::MYSQL_ATTR_SSL_CERT] = $sslCert;
            }
            if (0 < strlen($sslCa)) {
                $options[\PDO::MYSQL_ATTR_SSL_CA] = $sslCa;
            }
            parent::__construct("$type:host=$host;dbname=$database", $username, $password, $options);
            $app = App::getInstance();
            if (null != $app) {
                $app->log("Db::connect() - Connected to $host ($database)", SNAP_LOG_DEBUG);
            }
            if ('utf-8' == $defaultCharset) {
                $defaultCharset = 'utf8';
            }
            $defaultCharset = preg_replace('/[^-a-z0-9_]/i', '', $defaultCharset);
            if (0 < strlen($defaultCharset)) {
                $app->log('Setting database charset to "'.$defaultCharset.'"...', SNAP_LOG_DEBUG);
                $this->exec('SET NAMES '.$defaultCharset);
            }
        } catch (PDOException $e) {
            if (null != $app) {
                $app->log("Db::connect() - Failed connecting to $type://$username:$password@$host/$database - ".$this->pdoDBHandle->ErrorMsg(), SNAP_LOG_ERROR);
            }
        }
        return $this;
    }

    public function close()
    {
        $this->pdoDBHandle = null;
    }
}
?>
