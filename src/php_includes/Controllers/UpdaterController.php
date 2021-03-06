<?php
/**
 * Created by ahahn94
 * on 14.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/Controller.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Updater/Updater.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Management/UserManager.php";

/**
 * Class UpdaterController
 * Implements the controller for the update of the database and cache.
 */
class UpdaterController implements Controller
{

    private static $CurrentPage = "";       // Current page. Specifies the menu entry to highlight.
    private $updaterRunning = true;         // UpdaterController will start the update (or it is already running).

    /**
     * Controller constructor.
     * @param $path array List of the parts of the path behind the controller name.
     * E.g. "controller/path/to/resource" becomes $controllerName="controller" and $path=array("path","to","resource".
     * @param $getParameters array List of the GET parameters behind the URL.
     */
    public function __construct($path, $getParameters)
    {
        /*
         * Nothing to do here.
         * Data processing has to happen
         * after the document is generated.
         */
    }

    /**
     * Generates the document based on a view
     * and the data passed to the constructor.
     */
    function generateDocument()
    {
        // Check if member of admins group. Send 403 - Forbidden if not.
        if (UserManager::getCurrentUser()["UserGroupID"] == UserManager::getGroupIDs()["admin"]){
            // Send view.
            ignore_user_abort(true);    // Ignore user closing the connection.

            /*
             * Disable the default time limit.
             * The updates will continue running after the view is send, which will take some time.
             */
            set_time_limit(0);

            ob_start(); // Start new output buffer for sending the view.
            header("Location: /updates");   // Redirect to /updates.
            header('Connection: close');
            header('Content-Length: ' . ob_get_length());
            header('Content-Encoding: none');
            ob_end_flush();
            flush();

            // Close session so loading of other pages from the session that started the updater do not block.
            session_write_close();

            // Start update in background.
            $updater = new Updater();
            $updater->updateAll();
        } else {
            require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/ForbiddenController.php";
            $controller = new ForbiddenController(array(), array());
            $controller->generateDocument();
        }
    }
}