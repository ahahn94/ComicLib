<?php
/**
 * Created by ahahn94
 * on 14.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/Controller.php";

/**
 * Class NotFoundController
 * Implements the controller for the "404 - Not Found" error message.
 */
class NotFoundController implements Controller
{

    /**
     * Controller constructor.
     * @param $path array List of the parts of the path behind the controller name.
     * E.g. "controller/path/to/resource" becomes $controllerName="controller" and $path=array("path","to","resource".
     * @param $getParameters array List of the GET parameters behind the URL.
     */
    public function __construct($path, $getParameters)
    {
        /*
         * Nothing to do here, since the parameters are not needed to display the error message.
         */
    }

    /**
     * Generates the document based on a view
     * and the data passed to the constructor.
     */
    function generateDocument()
    {
        http_response_code(404);
        include $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Views/NotFoundView.php";
    }
}