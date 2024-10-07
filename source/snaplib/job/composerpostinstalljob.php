<?php 
Namespace Snap\job;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

/**
 * This class is used to do configuration for the RBAC (Role Based Access Control) package that we are
 * using. The package needs a PHP file to load its DB information and this script will provide the file 
 * for it.
 *
 * @author  Devon <devon@silverstream.my>
 * @ceratedon 2018/6/18 7:39 AM 
 * @package  snap.job
 */
class composerpostinstalljob
{
	public static function updateRBACDatabaseSetting(Event $event) {
		$vendorDir = $event->getComposer()->getConfig()->get('vendor-dir');
		//Production use.
		$filename = $vendorDir . '/owasp/phprbac/PhpRbac/database/database.config';
		file_put_contents($filename, "<?php\n\t" .
					"\$adapter=\"pdo_mysql\";\n\t" .
					"\$config=\Snap\App::getInstance()->getConfig();\n\t" .
					"list(\$host,\$port)=explode(':',\$config->{'snap.db.host'});\n\t" .
					"\$user=\$config->{'snap.db.username'};\n\t" .
					"\$pass=\$config->{'snap.db.password'};\n\t" .
					"\$dbname=\$config->{'snap.db.name'};\n\t" .
					"\$tablePrefix = 'rbac_';\n?>"
				);

		//For testing use.
		$filename = $vendorDir . '/owasp/phprbac/PhpRbac/tests/database/database.config';
		file_put_contents($filename, "<?php\n\t" .
					"\$adapter=\"pdo_mysql\";\n\t" .
					"\$config=new \Snap\config('".dirname(dirname(dirname(__FILE__)))."' . DIRECTORY_SEPARATOR . 'tests/testconfig.ini');\n\t" .
					"\$config->load();\n\t" .
					"list(\$host,\$port)=explode(':',\$config->{'snap.db.host'});\n\t" .
					"\$user=\$config->{'snap.db.username'};\n\t" .
					"\$pass=\$config->{'snap.db.password'};\n\t" .
					"\$dbname=\$config->{'snap.db.name'};\n\t" .
					"\$tablePrefix = 'test_rbac_';\n?>");
		//Also remove the setup.php file
		if(file_exists( $vendorDir . '/owasp/phprbac/PhpRbac/install.php')) unlink( $vendorDir . '/owasp/phprbac/PhpRbac/install.php');
	}
}
?>