<?php
/**
 * Created by ahahn94
 * on 14.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/Controller.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/VolumeReadStatus.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/ReadStatus.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Caching/ImageCache.php";

/**
 * Class VolumesController
 * Implements the controller for the volumes overview.
 */
class VolumesController implements Controller
{

    private static $CurrentPage = "volumes";    // Current page. Specifies the menu entry to highlight.
    private static $CachePath = "";             // Path to the image cache.
    private $volumes = array();                 // Volumes to show in the view.

    /**
     * Controller constructor.
     * @param $path array List of the parts of the path behind the controller name.
     * E.g. "controller/path/to/resource" becomes $controllerName="controller" and $path=array("path","to","resource".
     * @param $getParameters array List of the GET parameters behind the URL.
     */
    public function __construct($path, $getParameters)
    {
        $userID = $_SESSION["User"]["UserID"];
        // Update ReadStatus if requested.
        if (!empty($_POST)) {
            $volumeID = $_POST["VolumeID"];
            $readStatus = $_POST["ReadStatus"];
            if (!empty($volumeID) && !empty($readStatus)) {
                if ($readStatus === "true" || $readStatus === "false") {
                    $readStatusRepo = new ReadStatus();
                    $readStatus = ($readStatus === "true") ? true : false;
                    $readStatusRepo->updateVolume($volumeID, $userID, $readStatus);
                }
            }
            // Redirect to same page to clear POST form data and enable going back inside the browser.
            header("Location: /" . $_GET["_url"]);
            exit();
        }

        // Prepare data for view.
        $volumesRepo = new VolumeReadStatus();
        $this->volumes = $volumesRepo->getAll($userID);
        self::$CachePath = ImageCache::getImageCachePath();
    }

    /**
     * Generates the document based on a view
     * and the data passed to the constructor.
     */
    function generateDocument()
    {
        // Check if there are volumes to show in the view.
        if (!empty($this->volumes)) {
            // There are volumes to show. Send view VolumesView.
            include $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Views/VolumesView.php";
        } else {
            // $this->volumes is empty. Show "Empty Database" view.
            include $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Views/EmptyDatabaseView.php";
        }
    }
}