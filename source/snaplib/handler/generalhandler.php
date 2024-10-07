<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2016
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use \Snap\store\dbdatastore as DbDatastore;
USe Snap\App;

/**
 * This handler is meant to be a general purpose PROGRAMMER specific quick diagnostics tool area
 * You can put all the system diagnostics or setting configuration here.  Use has to have a proper session
 * to access this page and some actions requiring /root/developer permission
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 */
class GeneralHandler extends basehandler {
	
	/**
	 * default implementation that assumes the every action key will have a corresponding permission
	 *
	 * @param  String  $action  Action requested by user
	 * @return String   The permission string representing the permissions to check for
	 */
	function getRights($action) {
		return "/all/access";
	}

	/**
	 * This method will determine is this particular handler is able to handle the action given.
	 * 
	 * @param  String $action The action name to be handled
	 * @return boolean         True if this handler is able to response to the particular action.  False otherwise.
	 */
	function canHandleAction( $app, $action) {
		$userObj = $app->getUserSession()->getUser();
		if($userObj && $userObj->id > 0 && method_exists($this, $action)) return true;
		return false;
	}

	/**
	 * This method adds in additional handler to form a composite handler chain that is able to
	 * perform certain types of actions.
	 * 
	 * @param IHandler $child The handler that would be added into.
	 */
	function addChild(\Snap\IHandler $child) {
		return false;
	}

	function doAction( $app, $action, $params) {

	}

	/**
	 * Method to list out all stores that are utilizes cached db classes and list out its cached data
	 * status and information.
	 * 
	 * @param  mxApp  $app    Application object.
	 * @param  array  $params Valid parameters includes 'resetcache' and 'expand'
	 * @return Cached datastores listing
	 */
	function drmemcache( $app, $params) {
		$storeObject = null;
		$href = "{$_SERVER['PHP_SELF']}?hdl=general&action=drmemcache";
		$href2 = "{$_SERVER['PHP_SELF']}?hdl=general&action=drmemcache&expand=";

		$storeNames = $app->getController()->getStoreNameList();
		//Add additional 'custom stores with the format of array (name, object)'
		// $cacheId = $app->getConfig()->isKeyExists('cacheid') ? $app->getConfig()->cacheid : '0';
		// $storeNames[] = array( 'appointmentcache', new \Snap\store\cachedatastore( $app->getCacher(), $cacheId, 'appt', '\Snap\object\keyvalueitem', true, 18000,false));

		//Process the additional action of actually showing cache info and resetting of cache data.
		if( isset($params['expand']) || isset($params['resetcache'])) {
			$storeNameParam = $params['expand'] . $params['resetcache'];
			$storeName = $storeNameParam;
			$subStoreName = '';
			if( preg_match('/(.*)_(.*)/', $storeNameParam, $matches)) {
				$storeName = $matches[1];
				$subStoreName = $matches[2];
			}
			$storeObject = null;
			if( in_array($storeName, $storeNames)) $storeObject = call_user_func_array(array($app, $storeName . 'Store'), []);
			else {
				for( $i = count($storeNames)-1; $i >= 0; $i--) {
					if( is_array($storeNames[$i]) && $storeNames[$i][0] == $storeName) {
						$storeObject = $storeNames[$i][1];
						break;
					}
				}
			}
			if(!$storeObject) echo "No store object found";
			if( 0 < strlen($subStoreName)) $storeObject = call_user_func_array(array($storeObject, 'getRelatedStore'), [$subStoreName]);
			if(isset($params['resetcache'])) $storeObject->refreshCache();
			else if(isset($params['expand'])) {
				echo $storeObject->describeCache();
				return;
			}
		}

		//First level listing of all the available stores that are cache enabled
		echo "<HTML><head><title>Dr Memcache</title>";
		echo "<script type=\"text/javascript\" src=\"js/jquery/jquery.min.js?v=v2.1.0\" charset=\"utf-8\"></script>";
		echo '<script>
		function ajaxGetMemCache(name){
			$.get( "'.$href2.'"+name,
			function( data ) {
				$( "#"+name+"_div" ).html( data );
			});
		}
		</script>';
		echo "</head>";
		echo"<BODY><H1><a href='$href'>Memcache Inspector</a><br></H1><hr><br>";


		foreach($storeNames as $aStoreName) {
			if( is_string($aStoreName) ) {
				$object = call_user_func_array(array($app, $aStoreName . 'Store'), []);
				$CheckingStores = [ $aStoreName => $object ];
			} else if( is_array($aStoreName) && $aStoreName[1] instanceof \Snap\IEntityStore) {
				$object = $aStoreName[1];
				$CheckingStores = [ $aStoreName[0] => $object ];
			}
			if(method_exists($object, 'getRelatedStoreList')) {
				$subStores = $object->getRelatedStoreList();
				foreach( $subStores as $subStoreName) {
					if( in_array( $subStoreName, $storeNames)) continue;
					$subObject = call_user_func_array(array($object, 'getRelatedStore'), [$subStoreName]);
					$CheckingStores[$aStoreName.'_'.$subStoreName] = $subObject;
				}
			}
			foreach( $CheckingStores as $name => $object) {
				$matched = preg_match('/cache/', get_class($object));
				if(!$matched) {
					$class = new \ReflectionClass(get_class($object));
					$matched = preg_match('/cache/', $class->getParentClass());
				}
				$realStoreName = $name;
				if( preg_match('/(.*)_(.*)/', $name, $matches)) {
					$realStoreName = $matches[2];
				}
				if( $object && $matched ) {
					echo "<h2>{$realStoreName} Store Cache <font size=-1> <input type='button' value='Get Data' onclick='ajaxGetMemCache(\"{$name}\");'/> <a href='$href&resetcache={$name}'>[Reset]</a></font></h2><hr><br>";
					echo "<div id='{$name}_div'></div>";				
				}
			}
		}
		echo "</BODY></HTML>";
	}

	function iniEncrypt($app, $params) {
		echo "<h1>Ini Config Encryption Tool:</h1><br><table>";
		$crypto = new \Snap\Crypt;
		$crypto->setCipher('tripledes'); 	// set the cipher
		$crypto->setMode('cfb'); 		// set encryption mode
		$crypto->setKey('!@^f_1VaN;OF#_+9D3V0N3ty'); 			// set key
		$str = 'ORGDATA = ' . $params['inidata'] . ' ==>  Encrypted is [ENC##' . trim(base64_encode($crypto->encrypt($params['inidata']))) . '##]';

		$href = "action=iniencrypt";

		echo <<<PPOPP
			<HTML> <BODY>
				<form action="{$_SERVER['PHP_SELF']}?hdl=general&action=iniencrypt" method="post">
				Ini Data: <br><textArea rows="15" cols="80" name="inidata">{$params['inidata']}</textArea><br>
				<input type="submit" value="Encrypt Data"/>
				</form>
				</table>
				<br>The Encrypted data is $str<br>
				<!--<a href='{$_SERVER['PHP_SELF']}?hdl=data&aot=general&$href'>Key in another ini data to encrypt</a>-->
			</BODY></html>
PPOPP;
	}
    
    function iniDecrypt ($app, $params) {
        echo "<h1>Ini Config Decryption Tool:</h1><br><table>";
		$crypto = new \Snap\Crypt;
		$crypto->setCipher('tripledes'); 	// set the cipher
		$crypto->setMode('cfb'); 		// set encryption mode
		$crypto->setKey('!@^f_1VaN;OF#_+9D3V0N3ty'); 			// set key
        $dataArr = Array();

        if (preg_match('/ENC##(.*)##$/', $params['encrypted'], $dataArr)) {
            $str = trim($crypto->decrypt(base64_decode($dataArr[1])));
        }
		

		echo <<<PPOPP
			<HTML> <BODY>
				<form action="{$_SERVER['PHP_SELF']}?hdl=general&action=inidecrypt" method="post">
				Ini Encrypted: <br><textArea rows="15" cols="80" name="encrypted">{$params['encrypted']}</textArea><br>
				<input type="submit" value="Decrypt Data"/>
				</form>
				</table>
				<br>The Decrypted data is $str<br>
			</BODY></html>
PPOPP;
    }
}