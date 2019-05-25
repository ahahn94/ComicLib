<?php
/**
 * Created by ahahn94
 * on 14.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/Controller.php";

/**
 * Class LoginController
 * Implements the controller for the login view.
 */
class LoginController implements Controller
{

    private static $CurrentPage = "";       // Current page. Specifies the menu entry to highlight.

    private $loginStatus = "";              // Status message of the login function. Empty or error message.

    /**
     * Controller constructor.
     * @param $path array List of the parts of the path behind the controller name.
     * E.g. "controller/path/to/resource" becomes $controllerName="controller" and $path=array("path","to","resource".
     * @param $getParameters array List of the GET parameters behind the URL.
     */
    public function __construct($path, $getParameters)
    {
        $this->loginStatus = $getParameters["LoginStatus"];
    }

    /**
     * Generates the document based on a view
     * and the data passed to the constructor.
     */
    function generateDocument()
    {
        include $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Views/LoginView.php";
    }
}