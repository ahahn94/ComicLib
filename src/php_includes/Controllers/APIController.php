<?php
/**
 * Created by ahahn94
 * on 25.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/Controller.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicLibAPI/API/APIGenerics.php";

/**
 * Class APIController
 * Implements the controller for the different versions of the ComicLib RESTful API.
 */
class APIController implements Controller
{

    private $APIControllersPath = null; // Path to the controllers for the different versions of the API.

    private $APIControllers = array("v1" => "APIControllerV1");   // List of the different versions of API controllers.

    /**
     * Controller constructor.
     * @param $path array List of the parts of the path behind the controller name.
     * E.g. "controller/path/to/resource" becomes $controllerName="controller" and $path=array("path","to","resource".
     * @param $getParameters array List of the GET parameters behind the URL.
     */
    public function __construct($path, $getParameters)
    {
        $this->APIControllersPath = $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicLibAPI/APIControllers/";
        // Get API version number.
        $apiVersion = $path[0];
        $restOfPath = array_slice($path, 1);
        if (in_array($apiVersion, array_keys($this->APIControllers))) {
            // Valid API version. Load controller.
            $controllerClassName = $this->APIControllers[$apiVersion];   // Name of the controller class.
            // Path to the controller class inside $APIControllersPath.
            $controllerClassPath = "$controllerClassName.php";
            require_once $this->APIControllersPath . $controllerClassPath;
            $controller = new $controllerClassName($restOfPath, $getParameters);
        } else {
            // Invalid API version. Send 404 Not Found.
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