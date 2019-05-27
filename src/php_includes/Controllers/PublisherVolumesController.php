<?php
/**
 * Created by ahahn94
 * on 14.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/Controller.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/VolumeReadStatus.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/ReadStatus.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Publishers.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicVineAPI/Processing/Processing.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Caching/ImageCache.php";

/**
 * Class PublisherVolumesController
 * Implements the controller for the publishers overview.
 */
class PublisherVolumesController implements Controller
{

    private static $CurrentPage = "publishers";     // Current page. Specifies the menu entry to highlight.
    private static $CachePath = "";                 // Path to the image cache.
    private $publisherVolumes = array();            // Volumes of the publisher to show in the view.
    private $publisher = array();                   // Publisher to show in the view.

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
        // Get the PublisherID from path (the path should be like /publisher/PublisherID, so $path[0] should contain the ID).
        if (!empty($publisherID = $path[0])) {
            $VolumeReadStatusRepo = new VolumeReadStatus();
            $this->publisherVolumes = $VolumeReadStatusRepo->getSelection($publisherID, $userID);
            $publishersRepo = new Publishers();
            $this->publisher = $publishersRepo->get($publisherID);
            self::$CachePath = ImageCache::getImageCachePath();
        }   // Else do nothing, generateDocument will show the "404 Not Found" view.
    }

    /**
     * Generates the document based on a view
     * and the data passed to the constructor.
     */
    function generateDocument()
    {
        // Check if there are volumes to show.
        if (!empty($this->publisherVolumes)) {
            // $publisherVolumes contains volumes. Show album view of volumes.
            include $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Views/PublisherVolumesView.php";
        } else {
            // $volumeIssues does not contain issues. Show "404 Not Found" view.
            include $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Views/NotFoundView.php";
        }
    }
}