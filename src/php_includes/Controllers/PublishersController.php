<?php
/**
 * Created by ahahn94
 * on 14.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/Controller.php";

/**
 * Class PublishersController
 * Implements the controller for the publishers overview.
 */
class PublishersController implements Controller
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
         * TODO: Implement preparing data for view.
         */
    }

    /**
     * Generates the document based on a view
     * and the data passed to the constructor.
     */
    function generateDocument()
    {
        // TODO: Implement generateDocument() method.
        include $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Views/PublishersView.php";
    }
}