<?php
/**
 * Created by ahahn94
 * on 14.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/Controller.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Issues.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Volumes.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Storage/StorageManager.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Storage/Downloader.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/NotFoundController.php";

/**
 * Class DownloadController
 * Implements the controller for the download of comic files.
 */
class DownloadController implements Controller
{

    /**
     * Controller constructor.
     * @param $path array List of the parts of the path behind the controller name.
     * E.g. "controller/path/to/resource" becomes $controllerName="controller" and $path=array("path","to","resource".
     * @param $getParameters array List of the GET parameters behind the URL.
     */
    public function __construct($path, $getParameters)
    {
        $issueID = $getParameters["IssueID"];

        /*
         * Try to get the issue matching IssueID from the database.
         */
        if (!empty($issueID)) {
            $issues = new Issues();
            $issue = $issues->get($issueID);
            if (!empty($issue)) {

                // Found issue. Get parent volume.
                $volumes = new Volumes();
                $volume = $volumes->get($issue["VolumeID"]);

                // Build path to the comic file.
                $filePath = StorageManager::getStoragePath() . "/" . $volume["VolumeLocalPath"] . "/" .
                    $issue["IssueLocalPath"];

                // Start download.
                Downloader::download($filePath);
            } else {
                // No issue matching IssueID. Show 404.
                $notFound = new NotFoundController(array(), array());
                $notFound->generateDocument();
            }
        } else {
            // Empty IssueID -> invalid URL. Show 404.
            $notFound = new NotFoundController(array(), array());
            $notFound->generateDocument();
        }

    }

    /**
     * Generates the document based on a view
     * and the data passed to the constructor.
     */
    function generateDocument()
    {
        /*
         * Nothing to do here, as no document has to be delivered.
         * The delivery of the file requested for download is already handled by Downloader.
         */
    }
}