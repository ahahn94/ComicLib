<?php
/**
 * Created by ahahn94
 * on 04.06.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/Controller.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/IssueReadStatus.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/ReadStatus.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Caching/ImageCache.php";

/**
 * Class ReadingListController
 * Implements the controller for the reading list overview.
 */
class ReadingListController implements Controller
{

    private static $CurrentPage = "readinglist";    // Current page. Specifies the menu entry to highlight.
    private static $CachePath = "";             // Path to the image cache.
    private $issues = array();                 // Issues to show in the view.

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
            // Turn readstatus into boolean.
            $readStatus = $_POST["ReadStatus"] === "true" ? true : ($_POST["ReadStatus"] === "false" ? false : "");
            $dataset = array("IsRead" => $readStatus, "CurrentPage" => 0);
            if (!empty($issueID)) {
                if ($readStatus === true || $readStatus === false) {
                    $readStatusRepo = new ReadStatus();
                    $readStatusRepo->updateIssue($issueID, $userID, $dataset);
                }
            }
            // Redirect to same page to clear POST form data and enable going back inside the browser.
            header("Location: /" . $_GET["_url"]);
            exit();
        }

        // Prepare data for view.
        $issuesReadStatusRepo = new IssueReadStatus();
        $issues = $issuesReadStatusRepo->getAll($userID);
        // Filter issues for issues that have IsRead = 0 and CurrentPage != 0.
        $this->issues = array_filter($issues, function ($issue) {
            return (($issue["IsRead"] === "0") && ($issue["CurrentPage"] !== "0"));
        });
        self::$CachePath = ImageCache::getImageCachePath();
    }

    /**
     * Generates the document based on a view
     * and the data passed to the constructor.
     */
    function generateDocument()
    {
        include $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Views/ReadingListView.php";
    }

}