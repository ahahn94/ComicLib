<?php
/**
 * Created by ahahn94
 * on 14.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/Controller.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/IssueReadStatus.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/ReadStatus.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Volumes.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Caching/ImageCache.php";

/**
 * Class VolumeIssuesController
 * Implements the controller for the volume issues overview.
 */
class VolumeIssuesController implements Controller
{

    private static $CurrentPage = "volumes";    // Current page. Specifies the menu entry to highlight.
    private static $CachePath = "";             // Path to the image cache.
    private $volumeIssues = array();            // Issues of the volume to show in the view.
    private $volume = array();                  // Volume to show in the view.

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
            $issueID = $_POST["IssueID"];
            $readStatus = $_POST["ReadStatus"];
            $dataset = array("IsRead" => $readStatus, "CurrentPage" => 0);
            if (!empty($issueID) && !empty($readStatus)) {
                if ($readStatus === "true" || $readStatus === "false") {
                    $readStatusRepo = new ReadStatus();
                    $readStatusRepo->updateIssue($issueID, $userID, $dataset);
                }
            }
            // Redirect to same page to clear POST form data and enable going back inside the browser.
            header("Location: /" . $_GET["_url"]);
            exit();
        }

        // Prepare data for view.
        // Get the VolumeID from path (the path should be like /volume/VolumeID, so $path[0] should contain the ID).
        if (!empty($volumeID = $path[0])) {
            $issuesReadStatusRepo = new IssueReadStatus();
            $this->volumeIssues = $issuesReadStatusRepo->getSelection($volumeID, $userID);
            $volumesRepo = new Volumes();
            $this->volume = $volumesRepo->get($volumeID);
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
        if (!empty($this->volumeIssues)) {
            // $volumeIssues contains issues. Show album view of issues.
            include $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Views/VolumeIssuesView.php";
        } else {
            // $volumeIssues does not contain issues. Show "404 Not Found" view.
            include $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Views/NotFoundView.php";
        }
    }
}