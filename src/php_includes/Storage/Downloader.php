<?php
/**
 * Created by ahahn94
 * on 15.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/NotFoundController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Logging/Logging.php";

/**
 * Class Downloader
 * Implements the download function. Downloads are resumable.
 */
class Downloader
{

    /**
     * Start download of the specified file.
     * Uses XSendFile to enable downloads from the otherwise locked storage directory.
     * storage is locked via .htaccess to require a login before downloading comics.
     * @param $filePath string Path to the file to download.
     */
    public static function download($filePath)
    {
        if (file_exists($filePath)) {
            $fileName = basename($filePath);    // Get filename from filepath.

            /*
             * Set and send headers for the download.
             */

            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $fileName);
            header('X-Sendfile: ' . $filePath);
            exit;

        } else {
            // File does not exist. Log error.
            Logging::logError("The file '$filePath' requested for download could not be found!");
            $notFound = new NotFoundController(array(), array());
            $notFound->generateDocument();
        }
    }
}