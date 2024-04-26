<?php
/**
 * Created by ahahn94
 * on 29.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/VolumeReadStatus.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Logging/Logging.php";

/**
 * Class ComicCache
 * Implements the functions for managing the comics cache.
 */
class ComicCache
{
    // Constants for the caching status.
    const COMIC_NOT_CACHED = 0;
    const COMIC_CACHING = 1;
    const COMIC_CACHED = 2;

    // Name of the lock file for caching issues.
    private $LockFileName = "caching.lock";

    // Accepted comic file and image formats.
    private $acceptedExtensions = array("pdf", "rar", "zip");
    private $convertableExtensions = array("cbz" => "zip", "cbr" => "rar");
    private $imageFormats = array("jpg", "jpeg", "png", "gif");

    private $VolumesRepo = null;

    // Local path and web path too the comics cache.
    private $ComicCacheAbsolutePath = "/var/www/html/cache/comics/";
    private $ComicCachePath = "/cache/comics/";

    // List of the file formats available for reading via the web-interface.
    private static $ReadableFileFormats = array("cbr", "cbz", "pdf", "rar", "zip");

    /**
     * ComicCache constructor.
     */
    public function __construct()
    {
        $this->VolumesRepo = new VolumeReadStatus();
    }

    /**
     * Either COMIC_NOT_CACHED, COMIC_CACHING or COMIC_CACHED.
     * @param $issue array Dataset of the issue to check caching for.
     * @return int Either COMIC_NOT_CACHED, COMIC_CACHING or COMIC_CACHED.
     */
    public function issueCached($issue)
    {
        $cachedIssueDirectory = $this->cachedIssueDirectory($issue["IssueID"]);
        if (is_dir($cachedIssueDirectory)) {
            // Directory exists. Check if cached or still caching.
            $file = fopen($cachedIssueDirectory . $this->LockFileName, "r");
            $wouldBlock = null;
            flock($file, LOCK_SH | LOCK_NB, $wouldBlock);
            fclose($file);
            if ($wouldBlock === 1) {
                // Caching is still in progress. Return COMIC_CACHING.
                return self::COMIC_CACHING;
            } else {
                // Caching is done. Return COMIC_CACHED.
                return self::COMIC_CACHED;
            }
        } else {
            // Directory does not exist. Return COMIC_NOT_CACHED.
            return self::COMIC_NOT_CACHED;
        }
    }

    /**
     * Add an issue to the cache.
     * @param $issue array Dataset of the issue to add to the cache.
     */
    public function cacheIssue($issue)
    {
        $startTime = microtime(true);   // Start time for the caching duration for the log.

        // Path to the cache directory for the issue.
        $cachedIssueDirectory = $this->ComicCacheAbsolutePath . "/" . $issue["IssueID"] . "/";

        // Create directory on the comics cache.
        if (mkdir($cachedIssueDirectory)) {
            // Successfully created directory.

            /*
             * Create lockfile and acquire lock.
             * Then, start caching.
             */
            $lockFilePath = $cachedIssueDirectory . $this->LockFileName;
            $file = fopen($lockFilePath, "w");
            if ($file !== false) {
                // Successfully opened (and created) file.
                if (flock($file, LOCK_EX | LOCK_NB)) {
                    // Successfully acquired lock -> signalising caching process.
                    // Start caching.
                    Logging::logInformation("Starting caching of comic file " . $issue["IssueLocalPath"] . ".");
                    $result = $this->addToCache($issue, $cachedIssueDirectory);
                    if ($result) {
                        // Successfully added files to cache.
                        $stopTime = microtime(true);
                        $diff = ($stopTime - $startTime);
                        Logging::logInformation("Finished caching of comic file " . $issue["IssueLocalPath"] .
                            " in " . number_format($diff) . "s.");
                    }
                    fclose($file);
                }
            } else {
                // Could not acquire file lock. Log error.
                Logging::logError("Could not acquire lock on " . $lockFilePath . "! Not adding comic " .
                    $issue["IssueLocalPath"] . " to cache. Can not generate read view.");
                return;
            }
        } else {
            // Else error creating directory.
            Logging::logError("Could not create cache directory for " . $issue["IssueLocalPath"] .
                "! Can not generate read view.");
            return;
        }
    }

    /**
     * Get the images of an already cached issue.
     * @param $issueID string IssueID of the issue to get the images from.
     * @return array List of the images in ascending order.
     */
    public function getCachedIssue($issueID)
    {
        // Get files list.
        $path = "";
        $cachedIssueDirectory = $this->cachedIssueDirectory($issueID);
        $files = array_values(array_diff(scandir($cachedIssueDirectory), array(".", "..")));

        if (sizeof($files) === 1 && is_dir($cachedIssueDirectory . $files[0])) {
            // Images are in the only directory inside $cachedIssueDirectory. Happens sometimes with cbr/cbz.
            $path = $files[0] . "/";
            $files = array_diff(scandir($cachedIssueDirectory . $path), array(".", ".."));
        }

        /*
         * Filter images from the directory content.
         */

        $imageFormats = $this->imageFormats;
        $images = array_filter($files, function ($item) use ($imageFormats) {
            $explodedBasename = explode(".", basename($item));
            return in_array(array_pop($explodedBasename), $imageFormats);
        });

        // Turn image names into URIs.
        $images = array_map(function ($item) use ($issueID, $path) {
            return $this->cachedIssueWebpath($issueID) . "/$path/$item";
        }, $images);
        return $images;
    }

    /**
     * Add an issue to the cache.
     * Does the heavy lifting for cacheIssue.
     * @param $issue array Dataset of the issue to add to the cache.
     * @param $cachedIssueDirectory string Path to extract the images to.
     * @return bool true if successfully added, else false.
     */
    private function addToCache($issue, $cachedIssueDirectory)
    {
        $volumeID = $issue["VolumeID"];
        $volume = $this->VolumesRepo->getSingleDataset($volumeID, "1");

        // Path to the original comic file inside storage.
        $originalFile = StorageManager::getStoragePath() . "/" . $volume["VolumeLocalPath"] . "/" . $issue["IssueLocalPath"];

        /*
         * Check if the file extension needs fixing.
         * Fix it if necessary.
         * (Unzip and unrar need .zip and .rar instead of .cbz and .cbr).
         */

        $explodedBasename = explode(".", basename($originalFile));
        $originalExtension = mb_strtolower(array_pop($explodedBasename));
        $tempExtension = "";

        // Check if extension is acceptable. Else try to convert.
        if (in_array($originalExtension, $this->acceptedExtensions)) {
            $tempExtension = $originalExtension;
        } else if (in_array($originalExtension, array_keys($this->convertableExtensions))) {
            $tempExtension = $this->convertableExtensions[$originalExtension];
        } else {
            // Extension is neither supported nor convertible. Log error and exit.
            Logging::logError("The comic file " . $originalFile .
                " has no supported file extension! Can not add to comic cache nor generate reading view.");
            return false;
        }

        if (!empty($tempExtension)) {
            // Supported extension. Start adding files to cache.

            // Path to the temporary file.
            $tempFile = $this->ComicCacheAbsolutePath . "/" . $issue["IssueID"] . "." . $tempExtension;

            // Copy original file to temporary file location.
            copy($originalFile, $tempFile);

            /*
             * Handle caching of the supported file formats.
             */

            if ($tempExtension === "zip") {
                // Unzip file.
                $unzip = new ZipArchive();
                if ($unzip->open($tempFile) === true) {
                    $unzip->extractTo($cachedIssueDirectory);
                    $unzip->close();
                    unlink($tempFile);      // Delete temporary file.
                    return true;
                } else {
                    // Error opening comic archive. Log error.
                    Logging::logError("Error opening the comic file " . $tempFile .
                        "! Can not add to comic cache nor generate reading view.");
                    unlink($tempFile);      // Delete temporary file.
                    return false;
                }
            }

            if ($tempExtension === "rar") {
                // Unrar file.
                if (($archive = RarArchive::open($tempFile)) !== false) {
                    $files = $archive->getEntries();
                    foreach ($files as $file) {
                        $file->extract($cachedIssueDirectory);
                    }
                    $archive->close();
                    unlink($tempFile);      // Delete temporary file.
                    return true;
                } else {
                    // Error opening comic archive. Log error.
                    Logging::logError("Error opening the comic file " . $tempFile .
                        "! Can not add to comic cache nor generate reading view.");
                    unlink($tempFile);      // Delete temporary file.
                    return false;
                }
            }

            if ($tempExtension === "pdf") {
                // Export pages from pdf as images.
                try {
                    // Create files with names of 5 digit integers and leading 0 padding.
                    $fileNameTemplate = $cachedIssueDirectory . "%05d.jpeg";
                    $imagick = new Imagick();
                    // Set memory limit to 512MBs (necessary to avoid missing pages after conversion).
                    $imagick->setResourceLimit(Imagick::RESOURCETYPE_MEMORY, 536870912);
                    // Set higher resolution, as the default one is not sufficient.
                    $imagick->setResolution(300, 300);
                    // Read the file.
                    $file = fopen($tempFile, "r");
                    $imagick->readImageFile($file);
                    // Set compression and format for export.
                    $imagick->setCompressionQuality(100);
                    $imagick->setImageFormat("jpeg");

                    // Write images to $cachedIssueDirectory.
                    $imagick->writeImages($fileNameTemplate, false);
                    unlink($tempFile);      // Delete temporary file.
                    return true;
                } catch (Exception $e) {
                    // Error exporting images from pdf. Log error.
                    Logging::logError("Error exporting images from " . $tempFile . ": " . $e->getMessage());
                    Logging::logError("Can not add to comic cache nor generate reading view.");
                    unlink($tempFile);      // Delete temporary file.
                    return false;
                }
            }
        }
        return false;
    }

    /**
     * Generate the local path to the cached files for the specified issueID.
     * @param $issueID string IssueID of the issue to get the path to.
     * @return string Local path to the cached files of the issue.
     */
    private function cachedIssueDirectory($issueID)
    {
        return $this->ComicCacheAbsolutePath . "/" . $issueID . "/";
    }

    /**
     * Generate the web path to the cached files for the specified issueID.
     * @param $issueID string IssueID of the issue to get the path to.
     * @return string Web path to the cached files of the issue.
     */
    private function cachedIssueWebpath($issueID)
    {
        return $this->ComicCachePath . "/" . $issueID . "/";
    }

    /**
     * Check if a file is available for reading via the web-interface.
     * @param $filePath string File path or name of the file to check.
     * @return boolean True if the format can be read, else false.
     */
    public static function isReadable($filePath)
    {
        $explodedBasename = explode(".", basename($filePath));
        $extension = mb_strtolower(array_pop($explodedBasename));
        return in_array($extension, self::$ReadableFileFormats);
    }

}