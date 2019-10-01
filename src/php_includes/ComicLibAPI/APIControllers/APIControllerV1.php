<?php
/**
 * Created by ahahn94
 * on 27.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/Controller.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Authentication/APIAuthentication.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicLibAPI/API/APIGenerics.php";

/**
 * Class APIv1Controller
 * Implements the controller for version 1 of the ComicLib RESTful API.
 */
class APIControllerV1
{
    private $ResourcesPath = null; // The path to the API resources php files.

    private $APIAuthentication = null;

    // List of the available subcontrollers.
    private $APIResources = array("tokens" => "V1Tokens", "issues" => "V1Issues", "publishers" => "V1Publishers",
        "volumes" => "V1Volumes", "online" => "V1Online");

    /**
     * Controller constructor.
     * @param $path array List of the parts of the path behind the controller name.
     * E.g. "controller/path/to/resource" becomes $controllerName="controller" and $path=array("path","to","resource".
     * @param $getParameters array List of the GET parameters behind the URL.
     */
    public function __construct($path, $getParameters)
    {
        $this->ResourcesPath = $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicLibAPI/API/v1/";

        /*
         * Select subcontroller based on the beginning of $path.
         */
        // Get controller name.
        $controllerName = $path[0];
        $restOfPath = array_slice($path, 1);    // Rest of the path after removing the controller name.

        // Check if the controller exists. If not, send 404 error.
        if (in_array($controllerName, array_keys($this->APIResources))) {
            if ($controllerName === "tokens") {
                // V1Token implements its own authentication.
                require_once $this->ResourcesPath . "V1Tokens.php";
                $controller = new V1Tokens($restOfPath, $getParameters, new APIAuthentication());
            } else {
                // Check Bearer Token authorization. Will set the $AuthenticatedUser if successful.
                $APIAuthentication = new APIAuthentication();
                $APIAuthentication->bearerTokenAuthentication();
                // Load resource. Authentication will be checked inside the resource.
                $controllerClassName = $this->APIResources[$controllerName];   // Name of the controller class.
                // Path to the controller class inside $ControllerPath.
                $controllerClassPath = "$controllerClassName.php";
                require_once $this->ResourcesPath . $controllerClassPath;
                $controller = new $controllerClassName($restOfPath, $getParameters, $APIAuthentication);
            }
        } else {
            // Controller does not exist. Send 404.
            APIGenerics::sendNotFound();
        }

    }
}