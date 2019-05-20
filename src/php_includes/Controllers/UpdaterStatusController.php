<?php
/**
 * Created by ahahn94
 * on 14.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/Controller.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Updater/Updater.php";

/**
 * Class UpdaterStatusController
 * Implements the controller for the updates status view.
 */
class UpdaterStatusController implements Controller
{

    private static $CurrentPage = "update";       // Current page. Specifies the menu entry to highlight.
    private $updaterRunning = false;              // Current status of the Updater.

    /**
     * Controller constructor.
     * @param $path array List of the parts of the path behind the controller name.
     * E.g. "controller/path/to/resource" becomes $controllerName="controller" and $path=array("path","to","resource".
     * @param $getParameters array List of the GET parameters behind the URL.
     */
    public function __construct($path, $getParameters)
    {
        // Get the updater status.
        $this->updaterRunning = Updater::updaterRunning();
    }

    /**
     * Generates the document based on a view
     * and the data passed to the constructor.
     */
    function generateDocument()
    {
        include $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Views/UpdaterStatusView.php";
    }
}