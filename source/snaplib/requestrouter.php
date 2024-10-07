<?php
/**
 * Copyright Silverstream Technology Sdn Bhd.
 * All Rights Reserved (c) 2018
 * @copyright Silverstream Technology Sdn Bhd. 2018
 */
Namespace Snap;

/**
* This class is meant for performing customised application command routing for actions that the users want to do.
* This class will determine which handler will be called to perform the required function
*
* @author  Devon Koh <devon@silverstream.my>
* @version 1.0
* @package  snap.base
*/
class RequestRouter
{
    const ROUTE_BY_PATH = 1, ROUTE_BY_QUERY = 2;

    protected $routeType;
    protected $handlerIdentifier;
    protected $actionIdentifier;
    protected $loginPageHandler;
    protected $expiredPageHandler;
    protected $startupPageHandler;
    protected $handlerPathPrefix = "\\Snap\\handler\\";
    protected $handlerPathSuffix = 'handler';
    protected $defaultHandlerMethod = 'doAction';
    protected $defaultAction = 'list';

    protected $customHandlerMap = array();
    /**
     * This method will create a request router instance to manage the routing of requests to a chosen handler class in
     * the framework.  You can initialise a generic version of the router which will automtically handle requests.
     *
     * @param constnat $routeType           The type of routing (path based / query based) that this router should act on
     * @param String   $handlerIdentifier   The index number / query key to indicate handler module to use.
     * @param String   $actionIdentifier    The index number / query key to indicate method to call or actions to use during method
     * @param String   $loginPageHandler     The default login page handler if session not found or expired
     * @param String   $expiredPageHandler   The handler class to manage session expiry
     * @param String   $startupPageHandler     The starting page handler to startup the app
     */
    public function __construct($routeType, $handlerIdentifier, $actionIdentifier, $loginPageHandler, $expiredPageHandler, $startupPageHandler)
    {
        $this->routeType = $routeType;
        $this->handlerIdentifier = is_numeric($handlerIdentifier) ? $handlerIdentifier : explode(',', $handlerIdentifier);
        $this->actionIdentifier = $actionIdentifier;
        $this->loginPageHandler = $loginPageHandler;
        $this->expiredPageHandler = $expiredPageHandler;
        $this->startupPageHandler = $startupPageHandler;
    }

    public function mapHandler($handlerParam, $handlerClassName)
    {
        $this->customHandlerMap[$handlerParam] = $handlerClassName;
    }

    public function routeToLoginPage(App $app)
    {
        return $this->handleRequest($app, $this->loginPageHandler, '');
    }

    public function routeToExpiredPage(App $app)
    {
        return $this->handleRequest($app, $this->expiredPageHandler, '');
    }

    public function routeToStartupPage(App $app)
    {
        return $this->handleRequest($app, $this->startupPageHandler, '');
    }

    public function route(App $app, $contextOrMode)
    {
        if (self::ROUTE_BY_PATH == $routeType) {
            $uri = $_SERVER['REQUEST_URI'];
            // Strip query string (?foo=bar) and decode URI
            if (false !== $pos = strpos($uri, '?')) {
                $uri = substr($uri, 0, $pos);
            }
            $uri = rawurldecode($uri);
            $splitData = explode('/', $uri);
            $handlerName = $splitData[$this->handlerIdentifier];
            $action = $plitData[$this->actionIdentifier];
            if (2 < count($splitData)) {
                $_REQUEST['id'] = $splitData[2];
            }
        } else {
            $ignoreHandlerNames  = explode(',', $app->getconfig()->{'snap.requestrouter.ignore.name'});
            foreach ($this->handlerIdentifier as $key) {
                if (isset($_REQUEST[$key]) && ! in_array($_REQUEST[$key], $ignoreHandlerNames)) {
                    $handlerName = $_REQUEST[$key];
                    break;
                }
            }
            if (isset($this->customHandlerMap[$this->handlerIdentifier])) {
                $handlerName = $this->customHandlerMap[$this->handlerIdentifier];
            }
            $action = $_REQUEST[$this->actionIdentifier];
            if ('' == $action) {
                $action = $this->defaultAction;
            }
        }
        if ('logout' == strtolower($handlerName)) {
            $app->getUserSession()->logout();
            echo json_encode(['success' => true, 'message' => 'go to login page please']);
            return;
        } elseif (1 == $_REQUEST['logout'] || 1 == $_POST['logout']) {
            $app->getUserSession()->logout();
            $cleanScriptName = preg_replace('/^\?(.*)$/', '', $_SERVER['SCRIPT_NAME']);
            header(sprintf('Location: %s://%s%s', $_SERVER["REQUEST_SCHEME"], $_SERVER['HTTP_HOST'], $cleanScriptName));
            exit;
        } elseif (0 == strlen($handlerName) && 0 < $app->getUsersession()->getUserId()) {
            $handlerName = $this->startupPageHandler;
        } elseif (0 == strlen($handlerName)) {
            $handlerName = $this->loginPageHandler;
        }
        return $this->handleRequest($app, $handlerName, $action);
    }

    protected function setHandlerPathPrefix($newPath)
    {
        $this->handlerPathPrefix = $newPath;
    }

    protected function setHandlerPathSuffix($newPath)
    {
        $this->handlerPathSuffix = $newPath;
    }

    protected function setDefaultHandlerMethod($methodName)
    {
        $this->defaultHandlerMethod = $methodName;
    }

    protected function getBrowserReturnParams(App $app, $action)
    {
        return $_REQUEST;
    }

    protected function handleRequest(App $app, $handlerName, $action)
    {
        $parameters = $this->getBrowserReturnParams($app, $action);
        if (! intval($this->handlerIdentifier)) {
            unset($parameters[$this->handlerIdentifier]);
        }
        if (! intval($this->actionIdentifier)) {
            unset($parameters[$this->actionIdentifier]);
        }
        if ($this->loginPageHandler == $handlerName) {
            unset($parameters['logout']);
        }
        try {
            $className = $handlerName;
            if (! preg_match("/^{$this->handlerPathPrefix}/", $className)) {
                $className = $this->handlerPathPrefix . $className;
            }
            if (! preg_match("/{$this->handlerPathSuffix}$/", $className)) {
                $className = $className . $this->handlerPathSuffix;
            }
            try {
                $r = new \ReflectionClass($className);
                $r = $r->getConstructor();
                if ($r && $r->getNumberOfParameters()) {
                    $handler = new $className($app);
                } else {
                    $handler = new $className();
                }
            } catch (\Exception $e) {
                $app->log("Requestrouter unable to find the class $className to launch with action $action and params " . http_build_query($parameters). "\nException thrown message is: " . $e->getMessage(), SNAP_LOG_ERROR);
                $app->log($e->getMessage(), SNAP_LOG_ERROR);
                echo "Sorry, I do not know how to process your request.  Please try again." . htmlspecialchars($e->getMessage());
                return;
            }
            if ($handler->canHandleAction($app, $action)) {
		$permission = $handler->getRights($action);

                if ($this->loginPageHandler != $handlerName && ! $this->isPermissionOK($app, $permission)) {
                    if ($app->getUsersession()->isExpired()) {
                        header("HTTP/1.0 401");
                        $app->getUsersession()->logout(true);
                        return;
                    }
                    $app->log("RequestRouter did not find proper permission [$permission] for user to continue.", SNAP_LOG_ERROR);
                    try {
                        $rbac = new \PhpRbac\Rbac();
                        $rbac->enforce($permission, $app->getUserSession()->getUserId());
                    } catch (\Exception $e) {
                        $app->log("##########Exception thrown when trying to get permission[$permission] for user " .$app->getUserSession()->getUsername()." to continue. Message: " . $e->getMessage(), SNAP_LOG_ERROR);
                        header('HTTP/1.0 403');
                        echo "You have no access to the system.  Please check with the administrator if you have have queries";
                        return;
                    }
                }
            } else {
                if ($app->getUsersession()->isExpired()) {
                    header("HTTP/1.0 401");
                    $app->getUsersession()->logout(true);
                    return;
                }
                $app->log("RequestRouter:  The handler $handlerName is unable to handle the requested action $action", SNAP_LOG_ERROR);
                header('HTTP/1.0 406');
                echo "The server does not know how to perform the requested action";
                return;
                // throw new \Exception("The handler $handlerName is unable to handle the requested action $action");
            }
        } catch (\Exception $e) {
            $app->log("RequestRouter caught exception with message " . $e->getMessage(), SNAP_LOG_ERROR);
            $app->log("RequestRouter unable to find appropriate handler for request " . $_SERVER['REQUEST_URI'] . " with handler name $handlerName. ", SNAP_LOG_ERROR);
            throw $e;
	}

	if($handlerName=='myaccountholder'){
	    $userId = $app->getUserSession()->getUserId();
            $app->log("User id when request to handle: ".$userId);
            $app->log("Validation for handler: ".($handlerName));
	    $app->log("Permission when handle request: ".$permission);
	}

        if (method_exists($handler, $action)) {
            $app->log("Request router found handler $handlerName and executing $action()...... ", SNAP_LOG_DEBUG);
            $data = call_user_func_array(array($handler, $action), array($app, $parameters));
        } else {
            $app->log("Request router found handler $handlerName and executing doAction('$action', ".json_encode($parameters).") ...... ", SNAP_LOG_DEBUG);
            $data = call_user_func_array(array($handler, $this->defaultHandlerMethod), array($app, $action, $parameters));
        }
        if (is_string($data)) {
            echo $data;
        }
    }

    /**
     * Checks whether the permission has been defined
     *
     * @param  App  $app
     * @param  String  $permission Permission that is needed fort the action
     * @return boolean             True if user has permission to perform action.  False otherwise.
     */
    protected function isPermissionOK($app, $permission)
    {
        return $app->hasPermission($permission);
    }
}
?>
