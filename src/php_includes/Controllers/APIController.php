<?php
/**
 * Created by ahahn94
 * on 25.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/Controller.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Authentication/APIAuthentication.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/API/APIGenerics.php";

/**
 * Class APIController
 * Implements the controller for the ComicLib RESTful API.
 */
class APIController implements Controller
{

    private $SubControllersPath = null; // The path to the subcontrollers php files.

    private $APIAuthentication = null;

    // List of the available subcontrollers.
    private $SubControllers = array("apikey" => "APIKeyController", "issues" => "APIIssuesController");

    /**
     * Controller constructor.
     * @param $path array List of the parts of the path behind the controller name.
     * E.g. "controller/path/to/resource" becomes $controllerName="controller" and $path=array("path","to","resource".
     * @param $getParameters array List of the GET parameters behind the URL.
     */
    public function __construct($path, $getParameters)
    {
        $this->SubControllersPath = $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/API/";

        /*
         * Select subcontroller based on the beginning of $path.
         */
        // Get controller name.
        $controllerName = $path[0];
        $restOfPath = array_slice($path, 1);    // Rest of the path after removing the controller name.

        // Check if the controller exists. If not, send 404 error.
        if (in_array($controllerName, array_keys($this->SubControllers))) {
            if ($controllerName === "apikey") {
                // APIKeyController implements its own authentication.
                require_once $this->SubControllersPath . "APIKeyController.php";
                $controller = new APIKeyController($restOfPath, $getParameters);
            } else {
                // Check Bearer Token authorization.
                $this->APIAuthentication = new APIAuthentication();
                $authorized = $this->APIAuthentication->bearerTokenAuthentication();
                if ($authorized === true) {
                    // Successfully authenticated. Process request.
                    $controllerClassName = $this->SubControllers[$controllerName];   // Name of the controller class.
                    // Path to the controller class inside $ControllerPath.
                    $controllerClassPath = "$controllerClassName.php";
                    require_once $this->SubControllersPath . $controllerClassPath;
                    $controller = new $controllerClassName($restOfPath, $getParameters);
                } else {
                    // Authentication failed. Send 401 Unauthorized.
                    APIGenerics::sendUnauthorized();
                }
            }
        } else {
            // Controller does not exist. Send 404.
            APIGenerics::sendNotFound();
        }

    }

    /**
     * Generates the document based on a view
     * and the data passed to the constructor.
     */
    function generateDocument()
    {
        /*
         * Nothing to do here, as this controller only redirects to other controllers.
         */
    }
}