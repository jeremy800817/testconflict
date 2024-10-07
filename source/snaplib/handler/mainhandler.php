<?php
//////////////////////////////////////////////////////////////////////
//
//  Copyright Silverstream Technology Sdn Bhd.
//  All Rights Reserved (c) 2019
//
//////////////////////////////////////////////////////////////////////
Namespace Snap\handler;

Use Snap\TLogging;
use Snap\IHandler;

/**
 * This class is meant to be a base class for all inherited based class to be built on.  These
 * classes is meant for performing requests by users
 *
 * @author Devon Koh <devon@silverstream.my>
 * @version 1.0
 * @package  snap.handler
 */
class mainhandler implements \Snap\IHandler
{
    Use TLogging;

    private $app = null;
    private $user = null;

    private $menu = array();
    private $menuID = array();
    private $homemenu = array();
    private $breadcrumb = '';
    private $params = array();
    private $currentMenuID = '';
    private $isDevelopment = false;
    private $isPartnerHasOwnAccount = false;

    public function __construct()
    {
        // // initialize masterright
        // mxApp::getInstance()->getMasterRight();
        if ('localhost' == $_SERVER['HTTP_HOST'] ||
            preg_match('/^(127|192|172|10|dev)\./', $_SERVER['HTTP_HOST']) ||
            'on' == \Snap\App::getInstance()->getConfig()->{'app.login.devMode'}) {
            $this->isDevelopment = true;
        }
        // $this->isDevelopment = true;
        $this->menu = array(
           
        );
        $this->homemenu = array(
           
        );
      
    }

   
   
    public function doAction($app, $action, $params) {
        $this->app = $app;
        $this->params = $params;
        $this->user = $app->getUserSession()->getUser();

       

        $mainHTML = $this->app->getConfig()->{'app.login.mainHtml'};
        if ($this->app->getConfig()->isKeyExists('app.login.mainDevHtml')) {
            if ($this->isDevelopment) {
                $mainHTML = $this->app->getConfig()->{'app.login.mainDevHtml'};
            }
        }		
        $mainHTML = SNAPAPP_DIR .DIRECTORY_SEPARATOR. $mainHTML;          
        $projectBase = $this->app->getProjectBase();
		$this->log('Will use mainHTML = '.$mainHTML);
        echo $this->getTemplate($mainHTML, $params);
        $_SESSION['blogin'] = false;
    }

    public function getTemplate($file, $params, $loadMenu = true) {
        // $extjsPath = 'js/ext-4.2.1.883/';
        // $extjsPath = 'js/ext-4.2.2.1144/';
        //$extjsPath = 'js/ext-4.2.3.1477/';
        $extjsPath = 'src/';
        if (strtolower($_SESSION['lang']) == 'zh_cn') $languageCode = 'zh_CN';
        else $languageCode = 'en'; // or zh_CN
        $topmenu = $homemenu = $favorites = $content = '';
        $mobileMode = false;
        if ($this->app->getConfig()->isKeyExists('mobileMode') && $this->app->getConfig()->mobileMode == true) {
            $this->log("Mobile mode enabled!");
            $mobileMode = true;
        }
		
        
       
			
        
       
      
        $preloadermask_css = $preloadermask_html = '';
        if ($_SESSION['blogin']) {
            $preloadermask_css = "<style type=\"text/css\"> HTML, BODY { height: 100%; } #myloading-mask {position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: #ffffff; z-index: 1000001; } #myloading {position: absolute; top: 40%; left: 45%; z-index: 1000002; width: 160px; } #myloading span {background: url('images/ajax-loader8.gif') no-repeat left center; padding: 0 0 35px 0; display: block; text-align: 'center'; color: #444; font-family: tahoma, arial, verdana; text-align: center;} </style>";
            $preloadermask_html = '<div id="myloading-mask"></div> <div id="myloading"> <span id="myloading-message">'.gettext('Loading, please wait...').'</span> </div>';
        }
        $this->log("Retrieving template '$file'...");
		
        $html = file_get_contents($file); 		
		return str_replace(
		array('##EXTJSPATH##'),
		array($extjsPath ),
		$html);
    }

    

    public function getTemplate2($file, $params) {
        return $this->getTemplate($file, $params, false);
    }

    

   
    /**
     * This method will return the rights that are applicable for this handler with this particular user type
     *
     * @param  String  $action  Action requested by user
     * @return String   The permission string representing the permissions to check for
     */
    public function getRights($action)
    {
        return '/all/access';
    }

    /**
     * This method will determine is this particular handler is able to handle the action given.
     *
     * @param  App    $app    The application object (for getting user session etc to test?)
     * @param  String $action The action name to be handled
     * @return boolean         True if this handler is able to response to the particular action.  False otherwise.
     */
    public function canHandleAction($app, $action)
    {
        return true;
    }

    /**
     * This method adds in additional handler to form a composite handler chain that is able to
     * perform certain types of actions.
     *
     * @param IHandler $child The handler that would be added into.
     */
    public function addChild(IHandler $child)
    {
    }
}
?>