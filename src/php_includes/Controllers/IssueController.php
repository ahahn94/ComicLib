<?php
/**
 * Created by ahahn94
 * on 30.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/Controller.php";

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/IssueReadStatus.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/ReadStatus.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Logging/Logging.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Storage/StorageManager.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Caching/ComicCache.php";

/**
 * Class IssueController
 * Implements the controller for the issues loading and reading views.
 */
class IssueController implements Controller
{

    private static $CurrentPage = "";       // Current page. Specifies the menu entry to highlight.

    private $cachingInProgress = false;     // Is adding of the issue to the cache still in progress?
    private $isPDF = false;                 // Is the issue file a pdf file? These take especially long to add to cache.

    private $images = array();              // List of the images of the issue to show in the reading view.
    private $currentPage = 0;               // Current page number of the issue. Continue reading there.

    /**
     * Controller constructor.
     * @param $path array List of the parts of the path behind the controller name.
     * E.g. "controller/path/to/resource" becomes $controllerName="controller" and $path=array("path","to","resource".
     * @param $getParameters array List of the GET parameters behind the URL.
     */
    public function __construct($path, $getParameters)
    {

        if (!empty($path)) {

            $issueID = $path[0];

            $comicCache = new ComicCache();
            $issuesRepo = new IssueReadStatus();
            $issue = $issuesRepo->getSingleDataset($issueID, "1");

            if (!empty($issue)) {

                /*
                 * Process POST form data.
                 */
                if (!empty($_POST)) {
                    // Received POST form data.

                    $newCurrentPage = $_POST["CurrentPage"]?? "";
                    $newIsRead = $_POST["IsRead"]?? "";

                    // Check if ReadStatus data is set.
                    if (isset($newCurrentPage) && isset($newIsRead)) {
                        /*
                         * Update database based on form data instead of sending reading view.
                         */

                        // Turn readstatus into boolean.
                        $newIsRead = ($newIsRead === "true") ? true : ($newIsRead === "false" ? false : "");
                        $newCurrentPage = ctype_digit($newCurrentPage) ? intval($newCurrentPage) : "";

                        if (($newIsRead !== "") && ($newCurrentPage !== "")) {
                            $readStatusRepo = new ReadStatus();
                            $userID = $_SESSION["User"]["UserID"];
                            $changed = gmdate("Y-m-d H:i:s");
                            $dataset = array("CurrentPage" => $newCurrentPage, "IsRead" => $newIsRead, "Changed" => $changed);
                            $readStatusRepo->updateIssue($issueID, $userID, $dataset);
                        }
                        exit(); // Exit here so no view is send (as updating the ReadStatus is a one way road).
                    }

                }
                // Requesting reading view. Prepare caching of issue and/or send reading view.

                // Check if the file extension of the comic file is pdf. Required to show a warning message that opening
                // pdfs takes extremely long.
                $explodedBasename = explode(".", basename($issue["IssueLocalPath"]));
                $extension = mb_strtolower(array_pop($explodedBasename));
                $this->isPDF = ($extension === "pdf") ? true : false;

                // Get caching status. Issue may not be cached or still caching, which will require waiting.
                // If the issue is already cached, the images can be send without further waiting.
                $isCached = $comicCache->issueCached($issue);

                switch ($isCached) {
                    case ComicCache::COMIC_NOT_CACHED:
                        // Start caching of comic images.

                        /*
                         * Disable the default time limit.
                         * The caching will continue running after the view is send, which will take some time.
                         */
                        set_time_limit(0);

                        ob_start(); // Start new output buffer for sending the view.
                        // Send the caching view.
                        include $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Views/IssueCachingView.php";
                        header('Connection: close');
                        header('Content-Length: ' . ob_get_length());
                        header('Content-Encoding: none');
                        ob_end_flush();
                        flush();

                        // Close session so loading of other pages from the session that started the caching do not block.
                        session_write_close();

                        // Start caching.
                        $comicCache->cacheIssue($issue);
                        exit();
                        break;
                    case ComicCache::COMIC_CACHED:
                        // Comic is already cached. Get images from cache for reading view.
                        $this->images = $comicCache->getCachedIssue($issueID);
                        $currentPage = $issue["CurrentPage"];
                        $this->currentPage = ($currentPage === "0") ? "1" : $currentPage; // Move to first page if not read.
                        break;
                    case ComicCache::COMIC_CACHING:
                        // Comic is still caching. Show loading view.
                        $this->cachingInProgress = true;
                }
            } // Else no issue was found. Show 404 Not Found.
        }
        // Else no issueID was received and 404 Not Found will be send.
    }

    /**
     * Generates the document based on a view
     * and the data passed to the constructor.
     */
    function generateDocument()
    {
        if ($this->cachingInProgress) {
            // Caching still in progress. Show caching view.
            include $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Views/IssueCachingView.php";
        } else {
            if (!empty($this->images)) {
                // Images where found. Show reading view.
                include $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Views/IssueReadingView.php";
            } else {
                // No images where found. No IssueID set, Issue does not exist or database error. Show 404 Not Found.
                require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/NotFoundController.php";
                $controller = new NotFoundController(array(), array());
                $controller->generateDocument();
            }
        }
    }
}