<?php
/**
 * Created by ahahn94
 * on 21.07.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/Controller.php";

/**
 * Class ForbiddenController
 * Implements the controller for the "403 - Forbidden" error message.
 */
class ForbiddenController implements Controller
{

    private static $CurrentPage = "";    // Current page. Specifies the menu entry to highlight.

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
        http_response_code(403);
        include $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Views/ForbiddenView.php";
    }
}