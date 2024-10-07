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
class gtpLoginHandler implements \Snap\IHandler
{
    Use TLogging;

    private $app = null;

    public function doAction($app, $action, $params)
    {       
        $this->app = $app;
        if ('captcha' == $action) {
            return $this->drawCaptcha();
        } elseif (1 == $params['preloader']) {
            // $extjsPath = 'js/ext-4.2.1.883/';
            // $extjsPath = 'js/ext-4.2.2.1144/';
            $extjsPath = 'src/';
            $isDevelopment = false;
            $preloaderLink = 'client/cache.html';
            if ('localhost' == $_SERVER['HTTP_HOST'] || preg_match('/^(192|172|10|127)\./', $_SERVER['HTTP_HOST'])) {
                $isDevelopment = true;
            }
            if ($isDevelopment) {
                $preloaderLink = 'client/cachedev.html';
            }			
            $html = file_get_contents($preloaderLink);
            $tags = array('##EXTJSPATH##');
            $fillers = array($extjsPath);
            $html = str_replace($tags, $fillers, $html);
            echo $html;
            return;
        }
       
       
        $html = file_get_contents(SNAPAPP_DIR . DIRECTORY_SEPARATOR . $app->getConfig()->{'app.login.defaultLoginHtml'});          
        $partnerLoginLink = $app->getConfig()->{'app.login.partnerURL'};
        $salt = $app->getUserSession()->getSalt();    
     /*    if (defined('SNAPAPP_MODE_BACKOFFICE_OPERATOR')) {           
            $subtitle = gettext('Operator Login');
            $html = preg_replace('/<!--IFOPERATOR\[(.*?)\]-->/', '$1', $html);
            $html = preg_replace('/<!--IFPARTNER\[.*?\]-->/', '', $html);
            $partnerType = gettext('Operator');
        } elseif (defined('SNAPAPP_MODE_BACKOFFICE_PARTNER')) {
            $subtitle = gettext('Partner Login');
            $html = preg_replace('/<!--IFOPERATOR\[.*?\]-->/', '', $html);
            $html = preg_replace('/<!--IFPARTNER\[(.*?)\]-->/', '$1', $html);
            $partnerType = gettext('Partner');
        } else {           
            echo $app->getErrorPage(gettext('Invalid app mode!'));
            return false;
        } */
        if (0 < strlen($params['errmsg']) || 0 < strlen($params['username'])) {							
							
            $params['errmsg'] = '<div class="alert" id="error"><span class="closebtn" onclick="this.parentElement.style.display="none";">&times;</span>'.gettext("Invalid username or password").'</div>';
            $html = preg_replace('/<!--IFERROR\[(.*?)\]-->/', '$1', $html);
        } else {
            $html = preg_replace('/<!--IFERROR\[(.*?)\]-->/', '', $html);
        }
       
        preg_match('/handler\/(.*)handler\.php$/', strtolower(__FILE__), $regs);
        $preloaderLink = $_SERVER['PHP_SELF']."?hdl={$regs[1]}&preloader=1";
        
        $captchaLink = $_SERVER['PHP_SELF']."?hdl={$regs[1]}&action=captcha";
       
        $css = $this->getCSS();
        $userLogo = $this->getPersonlizedLogo($this->getShortcutAccess());        
        $formFields = $this->getFormFields($this->getShortcutAccess(), $params);
       
       
        $altLink = $this->switchAlternativeLink($this->getShortcutAccess());
        
       
        $tags = array('##SELFURL##', '##TITLE##', '##SUBTITLE##', '##SIGNINGIN_TEXT##', '##USERNAME_TEXT##',
        '##PASSWORD_TEXT##', '##CAPTCHA_TEXT##', '##LOGIN_TEXT##', '##ERROR_TEXT##',
        '##COPYRIGHT_TEXT##', '##PARTNERCODE_TEXT##', '##PARTNERLOGIN_LINK##', '##PARTNERLOGIN_TEXT##',
        '##SALT##', '##USERNAME##', '##CODE##', '##ERRORMSG##', '##PRELOADER_LINK##','##CSS##',
        '##USERLOGO##', '##FORMFIELDS##', '##ALTLINK##', '##LOGO_TEXT##', '##PARTNER_TYPE##', '##CAPTCHAURL##');
        $fillers = array($_SERVER['PHP_SELF'], $app->getAppName(), $subtitle, gettext('Signing-in, please wait...'),
           gettext('Username'), gettext('Password'), gettext('Captcha'), gettext('Login'),
           $params['errmsg'], $app->getCopyright(), gettext('Code'), $partnerLoginLink,
           gettext('Partner Login'), $salt, htmlspecialchars(trim(strtolower($_POST['username']))),
           htmlspecialchars(trim(strtolower($_POST['code']))), $errmsg, $preloaderLink, $css,
           $userLogo, $formFields, $altLink, gettext('Online Payment Made Simple'), $partnerType,
           $captchaLink);
        $html = str_replace($tags, $fillers, $html);
        echo $html;
    }

    private function getShortcutAccess()
    {
        $str = basename($_SERVER['PHP_SELF'], '.php');
        if ('smoothoperator' == $str) {
            $str = 'operator';
        } elseif ('smoothmerchant' == $str) {
            $str = 'merchant';
        } elseif ('aroperator' == $str) {
            $str = 'operator';
        }
        return $str;
    }

    private function getFormFields($userType, $params)
    {
        $form = array(
            'operator' =>  array('username' => gettext('Username'), 'clrpasswd' => gettext('Password')),
            'gateway' =>  array('username' => gettext('Username'), 'clrpasswd' => gettext('Password')),
            'partner' =>  array('code' => gettext('Code'),'username' => gettext('Username'), 'clrpasswd' => gettext('Password')),
            'merchant' =>  array('code' => gettext('Code'),'username' => gettext('Username'), 'clrpasswd' => gettext('Password')),
            'referral' =>  array('code' => gettext('Code'), 'username' => gettext('Username'), 'clrpasswd' => gettext('Password')),
            'agent' =>  array('code' => gettext('Code'),'username' => gettext('Username'), 'clrpasswd' => gettext('Password'))
            );

        return $this->buildFields($form[$userType], $params);
    }

    private function buildFields($fields = array(), $params)
    {
        $html = '';
        if (preg_match('/operator|gateway/', $this->getShortcutAccess())) {
            $html .= '<div>&nbsp;</div>';
        }
        foreach ($fields as $key => $value) {
            if ('clrpasswd' == $key) {
                $html .= '<div><span id="'.$key.'_text">'.$value.' :</span> <input type="password" class="login-input" id="'.$key.'" name="'.$key.'" value=""/></div>';
            } else {
                $html .= '<div><span id="'.$key.'_text">'.$value.' :</span> <input type="text" class="login-input" id="'.$key.'" name="'.$key.'" value="'.$params[$key].'"/></div>';
            }
        }
        return $html;
    }

    private function getPersonlizedLogo($userType)
    {
        $form = array(
            'operator' => '<div class="login-icon icon-'.$userType.'">'.gettext('Operator').'</div>',
            'gateway' => '<div class="login-icon icon-'.$userType.'">'.gettext('Gateway').'</div>',
            'partner' => '<div class="login-icon icon-'.$userType.'">'.gettext('Merchant').'</div>',
            'merchant' => '<div class="login-icon icon-'.$userType.'">'.gettext('Merchant').'</div>',
            'referral' => '<div class="login-icon icon-'.$userType.'">'.gettext('Referral').'</div>',
            'agent' => '<div class="login-icon icon-'.$userType.'">'.gettext('Agent').'</div>'
            );

        return $form[$userType];
    }

    private function switchAlternativeLink($userType)
    {
        $link = '';
        if ('operator' == $userType) {
            $link = '<a href="partner.php"><img width="22" src="images/login/logo_merchant.png"/>'.gettext('Merchant').'</a>';
        } elseif ('Partner' == $userType) {
            //$link = '<a href="operator.php"><img width="22" src="images/login/logo_operator.png"/>'.gettext('Operator').'</a>';
        } else {
            $link = '&nbsp;';
        }
        return $link;
    }
    
    private function getBrowser()
    {
        $u_agent = $_SERVER['HTTP_USER_AGENT'];
        $bname = 'Unknown';
        $platform = 'Unknown';
        $version= "";

        //First get the platform?
        if (preg_match('/linux/i', $u_agent)) {
            $platform = 'linux';
        } elseif (preg_match('/macintosh|mac os x/i', $u_agent)) {
            $platform = 'mac';
        } elseif (preg_match('/windows|win32/i', $u_agent)) {
            $platform = 'windows';
        }

        // Next get the name of the useragent yes seperately and for good reason
        if (preg_match('/MSIE/i', $u_agent) && ! preg_match('/Opera/i', $u_agent)) {
            $bname = 'Internet Explorer';
            $ub = "MSIE";
        } elseif (preg_match('/Firefox/i', $u_agent)) {
            $bname = 'Mozilla Firefox';
            $ub = "Firefox";
        } elseif (preg_match('/Chrome/i', $u_agent)) {
            $bname = 'Google Chrome';
            $ub = "Chrome";
        } elseif (preg_match('/Safari/i', $u_agent)) {
            $bname = 'Apple Safari';
            $ub = "Safari";
        } elseif (preg_match('/Opera/i', $u_agent)) {
            $bname = 'Opera';
            $ub = "Opera";
        } elseif (preg_match('/Netscape/i', $u_agent)) {
            $bname = 'Netscape';
            $ub = "Netscape";
        }

        // finally get the correct version number
        $known = array('Version', $ub, 'other');
        $pattern = '#(?<browser>' . join('|', $known) . ')[/ ]+(?<version>[0-9.|a-zA-Z.]*)#';
        if (! preg_match_all($pattern, $u_agent, $matches)) {
            // we have no matching number just continue
        }

        // see how many we have
        $i = count($matches['browser']);
        if (1 != $i) {
            //we will have two since we are not using 'other' argument yet
            //see if version is before or after the name
            if (strripos($u_agent, "Version") < strripos($u_agent, $ub)) {
                $version= $matches['version'][0];
            } else {
                $version= $matches['version'][1];
            }
        } else {
            $version= $matches['version'][0];
        }

        // check if we have a number
        if (null == $version || "" == $version) {
            $version="?";
        }

        return array(
            'userAgent' => $u_agent,
            'name'      => $bname,
            'majorName' => $ub,
            'version'   => $version,
            'platform'  => $platform,
            'pattern'    => $pattern
        );
    }
    
    private function getCSS()
    {
        $browser = $this->getBrowser();
        if ('MSIE' == $browser['majorName']) {
            // return msie css
            return "<style type=\"text/css\">
                    body {
                        background-color: #9F032F;
                        margin: 0px;
                        padding: 0px;
                        /*font: 14px/18px 'Lucida Grande', 'Lucida Sans Unicode', Helvetica, Arial, Verdana, sans-serif; */
                        font: 14px/18px tahoma,Arial,Verdana,sans-serif;
                        color: #737476;
                        height: 100%;
                    }
                    a, a:visited, a:link{
                        text-decoration: none;
                        color: #737476;
                    }
                    a:hover{
                        color: #000;
                    }
                    .container{
                        height: 100%;
                        width: 100%;
                        padding: 0;
                        margin: 0;
                    }
                    .container-top{
                        height: 50%;
                        width: 100%;
                        background-color: #FFF;
                        background: #FFF url('images/login/background.png') repeat-x top;
                    }
                    .container-login{
                        width: 100%;
                        height: 50%;
                        text-align: center;
                    }
                    .login-box{
                        width: 482px;
                        height: 325px;
                        margin-left: -241px;
                        margin-top: -162px;
                        position: absolute;
                        top: 50%;
                        left: 50%;
                        background: url('images/login/loginbox.png') no-repeat;
                    }
                    .login-box-footer{
                        margin-top: 65px;
                        clear: both;
                        background: url('images/login/reflection.png') left no-repeat;
                        height: 117px;
                        color: #bbbdb0;
                        font-size: 12px;
                    }
                    .login-box-footer div {
                        padding-top: 20px;
                    }
                    .login-bar{
                        background: url('images/login/clip_blank.png') left no-repeat;
                        height: 77px;
                        margin-left: -13px;
                        margin-top: 15px;
                        padding-top: 18px;
                        padding-left: 10px;
                        text-align: left;
                        width: 100%;
                        z-index: 1000;
                    }
                    .login-icon{
                        width: 180px;
                        height: 43px;
                        float: left;
                        padding-left: 45px;
                        line-height:2.7em;
                    }

                    .icon-agent{
                        background: url('images/login/logo_agent.png') left no-repeat;
                    }
                    .icon-gateway{
                        background: url('images/login/logo_gateway.png') left no-repeat;
                    }
                    .icon-partner{
                        background: url('images/login/logo_merchant.png') left no-repeat;
                    }
                    .icon-merchant{
                        background: url('images/login/logo_merchant.png') left no-repeat;
                    }
                    .icon-operator{
                        background: url('images/login/logo_operator.png') left no-repeat;
                    }
                    .icon-referral{
                        background: url('images/login/logo_referral.png') left no-repeat;
                    }
                    .flag{
                        float:right;
                        padding-right: 40px;

                    }
                    .flag table tr td{
                        padding-left: 6px;
                        font-size: 12px;
                        text-align: center;
                    }
                    .login-form{
                        clear:both;
                        text-align: right;
                        padding-right: 120;
                        height: 120px;
                    }
                    .login-form div {
                        padding: 2px;
                        width: 100%;
                    }
                    .login-form div span {
                        width: 150px;
                        display: inline-block;
                        text-align: right;
                    }
                    .login-input{
                        width: 150px;
                        height: 20;
                        background-color: #f0f0f0;
                    }
                    .captcha {
                        width: 49px;
                        height: 20;
                        background-color: #f0f0f0;
                    }
                    .captcha-img{
                        vertical-align: top;
                        padding-left: 5px;
                        margin-left: 5px;
                        margin-top: 0px
                        padding-top: 0px;
                    }
                    .captcha-reload{
                        display: inline-block;
                        width: 20px;
                        height: 20px;
                    }
                    .captcha-reload img{
                        width: 20px;
                        height: 20px;
                        display: inline-block;
                        vertical-align: top;
                        border: 0px;
                    }
                    .login-form div span, .login-form div input{
                        font-size:14px;
                    }
                    .login-footer div {
                        width: 40%;
                    }
                    .login-footer div img{
                        vertical-align: middle;
                        padding-right: 5px;
                    }
                    .login-footer div input {
                        background:url('images/login/login_button.png') no-repeat;
                        cursor:pointer;
                        width: 95px;
                        height: 27px;
                        border: none;
                        font-size: 14px;
                        color: #FFF;
                    }
                    .align-left{
                        float: left;
                        text-align: left;
                        padding-left: 30px;
                    }
                    .align-left img {border: 0;}
                    .align-right {
                        float: right;
                        text-align: right;
                        padding-right: 60px;
                    }
                    .clear-box{
                        height: 20px;
                    }
                    .logo{
                        background:url('images/login/eeziepaylogo.png') no-repeat;
                        float: right;
                        margin-top: 20px;
                        margin-right: 20px;
                        width: 200px;
                        height: 92px;
                    }
                    .error{
                        height: 14px;
                        color:red;
                        font-size: 12px;
                    }
                    .error div{
                        width: 152px;
                        display:inline;
                        text-align:left;
                    }
                    </style>";
        } else {
            // return other browser css
            return "<style type=\"text/css\">
                    body {
                        background-color: #9F032F;
                        margin: 0px;
                        padding: 0px;
                        /*font: 14px/18px 'Lucida Grande', 'Lucida Sans Unicode', Helvetica, Arial, Verdana, sans-serif; */
                        font: 14px/18px tahoma,Arial,Verdana,sans-serif;
                        color: #737476;
                        height: 100%;
                    }
                    a, a:visited, a:link{
                        text-decoration: none;
                        color: #737476;
                    }
                    a:hover{
                        color: #000;
                    }
                    .container{
                        height: 100%;
                        width: 100%;
                        padding: 0;
                        margin: 0;
                    }
                    .container-top{
                        height: 50%;
                        width: 100%;
                        background: #FFF url('images/login/background.png') repeat-x top;
                    }
                    .container-login{
                        width: 100%;
                        height: 50%;
                        text-align: center;
                    }

                    .login-box{
                        width: 482px;
                        height: 325px;
                        margin-left: -241px;
                        margin-top: -162px;
                        position: fixed;
                        top: 50%;
                        left: 50%;
                        background: url('images/login/loginbox.png') no-repeat;
                    }
                    .login-box-footer{
                        margin-top: 40px;
                        clear: both;
                        background: url('images/login/reflection.png') left no-repeat;
                        height: 117px;
                        color: #bbbdb0;
                        font-size: 12px;
                    }
                    .login-box-footer div {
                        padding-top: 20px;
                    }
                    .login-bar{
                        background: url('images/login/clip_blank.png') left no-repeat;
                        height: 77px;
                        margin-left: -6px;
                        margin-top: 15px;
                        padding-top: 28px;
                        padding-left: 10px;
                        text-align: left;
                        width: 100%;
                    }

                    .login-icon{
                        width: 180px;
                        height: 43px;
                        float: left;
                        padding-left: 45px;
                        line-height:2.7em;
                    }

                    .icon-agent{
                        background: url('images/login/logo_agent.png') left no-repeat;
                    }
                    .icon-gateway{
                        background: url('images/login/logo_gateway.png') left no-repeat;
                    }
                    .icon-partner{
                        background: url('images/login/logo_merchant.png') left no-repeat;
                    }
                    .icon-merchant{
                        background: url('images/login/logo_merchant.png') left no-repeat;
                    }
                    .icon-operator{
                        background: url('images/login/logo_operator.png') left no-repeat;
                    }
                    .icon-referral{
                        background: url('images/login/logo_referral.png') left no-repeat;
                    }
                    .flag{
                        float:right;
                        padding-right: 40px;

                    }
                    .flag table tr td{
                        padding-left: 6px;
                        font-size: 12px;
                        text-align: center;
                    }
                    .login-form{
                        clear:both;
                        text-align: right;
                        padding-right: 120;
                        height: 120px;
                    }
                    .login-form div {
                        padding: 2px;
                        width: 100%;
                    }
                    .login-form div span {
                        width: 150px;
                        display: inline-block;
                        text-align: right;
                    }
                    .login-input{
                        width: 150px;
                        height: 20;
                        background-color: #f0f0f0;
                    }
                    .captcha {
                        width: 49px;
                        height: 20;
                        background-color: #f0f0f0;
                    }
                    .captcha-img{
                        vertical-align: top;
                        padding-left: 5px;
                    }
                    .captcha-reload{
                        display: inline-block;
                        width: 20px;
                        height: 20px;
                    }
                    .captcha-reload img{
                        width: 20px;
                        height: 20px;
                        display: inline-block;
                        vertical-align: middle;
                    }
                    .login-form div span, .login-form div input{
                        font-size:14px;
                    }
                    .login-footer div {
                        width: 40%;
                    }
                    .login-footer div img{
                        vertical-align: middle;
                        padding-right: 5px;
                    }
                    .login-footer div input {
                        background:url('images/login/login_button.png') no-repeat;
                        cursor:pointer;
                        width: 95px;
                        height: 27px;
                        border: none;
                        font-size: 14px;
                        color: #FFF;
                    }
                    .align-left{
                        float: left;
                        text-align: left;
                        padding-left: 30px;
                    }
                    .align-left img {border: 0;}
                    .align-right {
                        float: right;
                        text-align: right;
                        padding-right: 60px;
                    }
                    .clear-box{
                        height: 25px;
                    }
                    .logo{
                        background:url('images/login/eeziepaylogo.png') no-repeat;
                        float: right;
                        margin-top: 20px;
                        margin-right: 20px;
                        width: 200px;
                        height: 92px;
                    }
                    .error{
                        height: 14px;
                        color:red;
                        font-size: 12px;
                        margin-bottom: 3px;
                    }
                    .error div{
                        width: 150px;
                        display:inline-block;
                        text-align:left;
                    }
                    </style>";
        }
    }

    private function drawCaptcha()
    {
        // require_once(SNAPLIB_DIR . 'adaptor/captcha.inc.php');
        $ttfFontPath = SNAPLIB_DIR . DIRECTORY_SEPARATOR.'resource'.DIRECTORY_SEPARATOR.'fonts'.DIRECTORY_SEPARATOR;  	
       
      // exit();
        $aFonts = array(
            $ttfFontPath.'arialbi.ttf',
            $ttfFontPath.'courbd.ttf',
            $ttfFontPath.'verdanab.ttf'
            //$ttfFontPath.'timesbi.ttf'
        );        
        $tw = 200;
        $th = 50;
        if (0 < $_REQUEST['tw']) {
            $tw =  $_REQUEST['tw'];
        }
        if (0 < $_REQUEST['th']) {
            $th =  $_REQUEST['th'];
        }
        $captcha = new \Snap\Captcha($aFonts, $tw, $th);     
        if (0 < strlen($_REQUEST['fkey'])) {
            $captcha->setCacher($app->getCacher());
            $captcha->setCacheKey(CAPTCHA_SESSION_ID.$_REQUEST['fkey']);
        }
        // $captcha->setBackgroundImages('images/captchabg'.sprintf('%02d', rand(1, 10)).'.jpg');
        // $captcha->setUseColour(false);
        $bUseColor = false;
        if ($this->app->getConfig()->isKeyExists('app.login.catcha.useColor') && 1 == $this->app->getConfig()->{'app.login.catcha.useColor'}) {
            $bUseColor = true;
        }
        $captcha->setUseColour($bUseColor);
        if (! $this->app->getConfig()->isKeyExists('app.login.catcha.useBg') || 0 == $this->app->getConfig()->{'app.login.catcha.useBg'}) {			
            $captcha->setBackgroundImages(SNAPAPP_DIR.DIRECTORY_SEPARATOR.'images'.DIRECTORY_SEPARATOR.'captchabg'.sprintf('%02d', rand(1, 10)).'.jpg');
        }
        $captcha->setCaseInsensitive(true);
        $captcha->setNumLines(15);
        //$captcha->setCharset('0-9,A-H,J-N,P-Y');
        //$captcha->setCharset('0-9');
        $captcha->setCharset('0,2,3,4,5,6,8,9');
        $captcha->setNumChars(4);
        $captcha->setMinFontSize(16);
        $captcha->setMaxFontSize(16);
        //$captcha->setShadow(true);   
           
        $captcha->create();
        
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