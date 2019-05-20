<?php
/**
 * Created by ahahn94
 * on 10.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Logging/Logging.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Issues.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Publishers.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Volumes.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/VolumeIssues.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicVineAPI/Resources/Issue.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicVineAPI/Resources/Publisher.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicVineAPI/Resources/Volume.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicVineAPI/Resources/VolumeIssue.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Caching/ImageCache.php";

/**
 * Class StorageManager
 * Handles management of the comics in src/storage.
 */
class StorageManager
{

    private static $StoragePath = "/var/www/html/storage";  // Path to the storage directory.
    // List of the ignored files and dirs inside storage $StoragePath.
    private static $IgnoredFilesAndDirs = array(".keep", ".", "..");
    // List of the ignored files and dirs inside volume directories.
    private static $IgnoredFilesAndDirsVolume = array("volume.ini", ".", "..");
    private static $VolumeIDFile = "volume.ini";
    private static $IssueNumberDelimiter = "#";

    // Database repositories.
    private $volumes = null;
    private $volumeIssues = null;
    private $publishers = null;
    private $issues = null;

    private $imageCache = null;

    /**
     * StorageManager constructor.
     */
    public function __construct()
    {
        // Initialize the repos once at construction.
        $this->volumes = new Volumes();
        $this->volumeIssues = new VolumeIssues();
        $this->publishers = new Publishers();
        $this->issues = new Issues();
        $this->imageCache = new ImageCache();
    }

    /**
     * Scan $StoragePath for directories containing comics.
     * Triggers adding to or updating database for valid directories.
     */
    public function scanStorage()
    {
        Logging::logInformation("Scanning comic storage...");
        $directories = scandir(self::$StoragePath);   // List of all files and directories of $StoragePath (non-recursive).
        $directories = array_diff($directories, self::$IgnoredFilesAndDirs);  // Remove ignored files and directories from list.

        foreach ($directories as $directory) {
            $relativeFilePath = $directory; // Relative path to the directory.
            $absoluteFilePath = self::$StoragePath . "/" . $directory;  // Absolute path to the directory.

            // Check if path is a directory.
            if (is_dir($absoluteFilePath)) {

                // Check if directory contains a $VolumeIDFile.
                $relativeVolumeIDFilePath = $relativeFilePath . "/" . self::$VolumeIDFile;
                $absoluteVolumeIDFilePath = $absoluteFilePath . "/" . self::$VolumeIDFile;
                if (is_file($absoluteVolumeIDFilePath)) {
                    // Check if $VolumeIDFile contains a VolumeID.
                    $fileContent = parse_ini_file($absoluteVolumeIDFilePath, true);
                    if ($fileContent != false) {
                        // File successfully parsed. Check if it contains a VolumeID.
                        $volumeID = $fileContent["Volume"]["ID"];
                        if (isset($volumeID)) {
                            // VolumeID successfully read. Pass to manageVolumeDirectory.
                            $this->manageVolumeDirectory($relativeFilePath, $volumeID);
                        } else {
                            // No VolumeID found inside $VolumeIDFile. Log error.
                            Logging::logError("No VolumeID found inside $relativeVolumeIDFilePath! Skipping $relativeFilePath.");
                        }
                    } else {
                        // Could not parse file. Log error.
                        Logging::logError("Could not parse $relativeVolumeIDFilePath! Skipping $relativeFilePath.");
                    }
                } else {
                    // Directory does not contain a $VolumeIDFile. Log that it will be ignored.
                    Logging::logWarning("Ignoring $relativeFilePath (does not contain " . self::$VolumeIDFile . ").");
                }
            } else {
                // Item is not a directory (may be a file or link). Log that it will be ignored.
                Logging::logWarning("Ignoring $relativeFilePath (not a directory).");
            }

        }

        Logging::logInformation("Finished scanning comic storage.");

    }

    /**
     * Manage adding and updating comics from a volume directory.
     * @param $volumePath string Relative path to the directory inside storage.
     * @param $volumeID string VolumeID from the $VolumeIDFile.
     */
    private function manageVolumeDirectory($volumePath, $volumeID)
    {

        Logging::logInformation("Scanning $volumePath for comic files...");

        // Check if volume is already on the database.
        $volumeFromDB = $this->volumes->get($volumeID);
        if (!empty($volumeFromDB)) {

            /*
             * Volume is already on the database. -> Publisher is already on the database.
             * Check if all issues inside this directory are already on the database.
             */

            $issueList = $this->volumeIssues->getSelection($volumeID);
            if (!empty($issueList)) {

                /*
                 * There are already some issues on the database. Compare to directory content.
                 */

                Logging::logInformation("$volumePath is already on the database. Scanning for new files...");

                // Get list of files inside $volumePath.
                $filesInDirectory = self::getFilesList($volumePath);

                // Get the list of files from $issueList.
                $issueFilesList = array_map(function ($item) {
                    return $item["IssueLocalPath"];
                }, $issueList);

                // Get files that are still missing on the database.
                $filesNotOnDB = array_diff($filesInDirectory, $issueFilesList);

                // Check if $filesNotOnDB is not empty to avoid getting the volume issues from ComicVine if not
                // necessary.
                if (!empty($filesNotOnDB)) {
                    // New files found. Match them to issues from ComicVine and add them to the database.

                    Logging::logInformation("$volumePath contains comic files that are not already on the database.");

                    /*
                     * Match $filesNotOnDB to issues.
                     */

                    // Get the list of issues of this volume from the ComicVine API.
                    $volumeIssuesAPI = VolumeIssue::get($volumeID);

                    $this->matchFilesToIssues($volumeFromDB, $filesNotOnDB, $volumeIssuesAPI);

                } else {
                    // All comic files of $volumePath are already on the database.
                    Logging::logInformation("All comic files of $volumePath are already on the database.");
                }

            }
        } else {

            /*
             * Volume is not on the database.
             * -> Volume and Issues have to be added.
             * Publisher might already exist on the database.
             */

            // Get volume dataset from ComicVine API.
            $volume = Volume::get($volumeID);

            if (!empty($volume)) {

                /*
                 * Check if publisher of volume is on the database.
                 * Add to database if not.
                 */
                $publisher = $this->publishers->get($volume["PublisherID"]);

                // Publisher not found on database. Get it from the ComicVine API and add it to the database.
                if (empty($publisher)) {
                    $publisher = Publisher::get($volume["PublisherID"]);
                    $this->publishers->add($publisher);

                    /*
                     * Update image on cache.
                     */
                    $this->imageCache->updatePublisherImage($publisher);
                }

                /*
                 * Add volume to database.
                 */

                // Add VolumeLocalPath and ReadStatus to dataset.
                $volume["ReadStatus"] = 0;
                $volume["VolumeLocalPath"] = $volumePath;

                // Add to database.
                $this->volumes->add($volume);

                /*
                * Update image on cache.
                */
                $this->imageCache->updateVolumeImage($volume);

                /*
                 * Get issue data for the comic files inside $volumePath.
                 */

                // Get the list of files inside $volumePath.
                $filesInDirectory = self::getFilesList($volumePath);

                // Get the list of issues of this volume from the ComicVine API.
                $volumeIssuesAPI = VolumeIssue::get($volumeID);

                // Match files to issues.
                $this->matchFilesToIssues($volume, $filesInDirectory, $volumeIssuesAPI);

            } else {
                // Error while requesting dataset from ComicVine API. Log error.
                Logging::logError("Could not read volume information for $volumePath");
            }
        }
    }

    /**
     * Get the list of file names inside $volumePath.
     * Checks for each element inside $volumePath if it actually is a file (and not a link or directory).
     * @param $volumePath string Path to get the file names from.
     * @return array Array of file names inside $volumePath. Empty if no files inside directory.
     */
    private static function getFilesList($volumePath)
    {
        // Get the list of files inside $volumePath.
        $directoryContent = scandir(self::$StoragePath . "/$volumePath");   // Content of the directory.
        $directoryWithoutIgnored = array_diff($directoryContent, self::$IgnoredFilesAndDirsVolume); // Without ignored.
        $filesInDirectory = // Only files contained in $directoryWithoutIgnored.
            array_filter($directoryWithoutIgnored, function ($item) use ($volumePath) {
                return is_file(self::$StoragePath . "/$volumePath/$item");
            });
        return $filesInDirectory;
    }

    /**
     * Match files inside a volumes directory to issues of the volume on the ComicVine API.
     * Add the successful matches to the database.
     * @param $volumeFromDB array Array with the volume dataset from the database.
     * @param $filesToMatch array List of the files in the volumes directory that have to be matched and added to DB.
     * @param $volumeIssuesFromAPI array List of the volumes issues from the ComicVine API.
     */
    private function matchFilesToIssues($volumeFromDB, $filesToMatch, $volumeIssuesFromAPI)
    {
        Logging::logInformation("Matching new comic files to issues from ComicVine...");

        // Match files to issues.
        foreach ($filesToMatch as $file) {

            // Check if filename contains $IssueNumberDelimiter.
            if (strpos($file, self::$IssueNumberDelimiter) !== false) {

                // Remove extension from the filename.
                $fileName = implode(array_slice(explode('.', $file), 0, -1));

                // Try to get the issue number from the file.
                // It should be the last part of the filename and be preceded by $IssueNumberDelimiter.
                // A classic issue number is an integer (1, 2, 3, etc.), but the ComicVine API also supports issue
                // numbers that contain non-digit characters (like 1/2, -1, 1a, etc.).
                $issueNumber = array_pop(explode(self::$IssueNumberDelimiter, $fileName));

                /*
                 * Get issue dataset from ComicVine API.
                 */

                $issue = array();   // Init issue as an empty array.

                // Search for the issue with the matching issue number.
                foreach ($volumeIssuesFromAPI["Issues"] as $volumeIssue) {

                    // Check if both numbers contain only digits.
                    // If they are, compare them as integers to avoid problems with leading 0s.
                    // Else, compare as strings.
                    if (ctype_digit($volumeIssue["IssueNumber"]) && ctype_digit($issueNumber)) {
                        if (intval($volumeIssue["IssueNumber"]) == intval($issueNumber)) {
                            $issue = $volumeIssue;
                            break;
                        }
                    } else {
                        if ($volumeIssue["IssueNumber"] == $issueNumber) {
                            $issue = $volumeIssue;
                            break;
                        }
                    }
                }

                if (!empty($issue)) {
                    // Successfully matched file to issue. Get details from ComicVine API.
                    $issue = Issue::get($issue["IssueID"]);

                    // Add IssueLocalPath and ReadStatus to dataset.
                    $issue["IssueLocalPath"] = $file;
                    $issue["ReadStatus"] = 0;

                    // Add to database.
                    $this->issues->add($issue);

                    /*
                    * Update image on cache.
                    */
                    $this->imageCache->updateIssueImage($issue);

                    Logging::logInformation("Added $file to the database.");

                } else {
                    // Issue number from file does not match any issue of the volume. Log error.
                    Logging::logError("Could not match " . $volumeFromDB["VolumeLocalPath"] . "/$file to any issue of the volume " .
                        $volumeFromDB["Name"] . "! Skipping file.");
                }

            } else {
                // No $IssueNumberDelimiter -> no valid issue number found. Log error.
                Logging::logError("No valid issue number in $file! Skipping.");
            }
        }

        Logging::logInformation("Finished matching comic files for " . $volumeFromDB["VolumeLocalPath"] . ".");

    }

    /*
     * Getters.
     */

    public static function getStoragePath(): string
    {
        return self::$StoragePath;
    }

}