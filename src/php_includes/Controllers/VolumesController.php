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

    // Variables for pagination.
    private $activePage;        // Number of the active page.
    private $pageCount;         // Number of pages available.
    private $previousPage;      // Number of the previous page, empty string of not exists.
    private $nextPage;          // Number of the next page, empty string of not exists.

    /**
     * Controller constructor.
     * @param $path array List of the parts of the path behind the controller name.
     * E.g. "controller/path/to/resource" becomes $controllerName="controller" and $path=array("path","to","resource".
     * @param $getParameters array List of the GET parameters behind the URL.
     */
    public function __construct($path, $getParameters)
    {
        $userID = $_SESSION["User"]["UserID"];

        // Check path for page number for pagination.
        $pageSize = 24;     // Number of volumes per page.
        $pageNumber = !empty($path[0]) ? $path[0] : "1";
        if (!ctype_digit($pageNumber)) {
            // Page number is not a number. Send 404 Not Found.
            require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/NotFoundController.php";
            $controller = new NotFoundController($path, $getParameters);
            $controller->generateDocument();
            exit();
        }

        // Update ReadStatus if requested.
        if (!empty($_POST)) {
            $volumeID = $_POST["VolumeID"];
            $readStatus = $_POST["ReadStatus"];
            if (!empty($volumeID) && !empty($readStatus)) {
                if ($readStatus === "true" || $readStatus === "false") {
                    $readStatusRepo = new ReadStatus();
                    $readStatus = ($readStatus === "true") ? true : false;
                    $changed = gmdate("Y-m-d H:i:s");
                    $readStatusRepo->updateVolume($volumeID, $userID, $readStatus, $changed);
                }
            }
            // Redirect to same page to clear POST form data and enable going back inside the browser.
            header("Location: /" . $_GET["_url"]);
            exit();
        }

        // Prepare data for view.
        $volumesRepo = new VolumeReadStatus();
        $volumes = $volumesRepo->getAll($userID);


        // Prepare pagination.
        $pageNumber = intval($pageNumber);  // Cast to int.
        $maximumPageCount = intval(ceil(sizeof($volumes) / $pageSize)); // use ceil to round up. Cast from float.

        $this->activePage = $pageNumber;
        $this->pageCount = $maximumPageCount;

        if ($pageNumber === 1) {
            $this->previousPage = "";   // No previous page.
        } else {
            $this->previousPage = $pageNumber - 1;
        }

        if ($pageNumber === $maximumPageCount) {
            $this->nextPage = "";   // No next page.
        } else {
            $this->nextPage = $pageNumber + 1;
        }

        if ($pageNumber > $maximumPageCount && $pageNumber != 1) {
            // Invalid page number (too big, so no content to display). Send 404 Not Found.
            require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/NotFoundController.php";
            $controller = new NotFoundController($path, $getParameters);
            $controller->generateDocument();
            exit();
        }

        // Get array slice with (pageNumber * pageSize) to ((pageNumber + 1) * pageSize - 1).
        $offset = ($pageNumber - 1) * $pageSize;
        $volumes = array_slice($volumes, $offset, $pageSize);

        $this->volumes = $volumes;
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