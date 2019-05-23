<?php
/**
 * Created by ahahn94
 * on 10.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Logging/Logging.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Issues.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Publishers.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/PublisherVolumes.php";
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
    private $publisherVolumes = null;

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
        $this->publisherVolumes = new PublisherVolumes();
        $this->imageCache = new ImageCache();
    }

    /**
     * Scan $StoragePath for directories containing comics.
     * Triggers adding to or updating database for valid directories.
     * Will remove issues and volumes from the database if they are no longer in $StoragePath.
     * If an volume becomes empty, it will be removed from the database.
     * If a publisher has no volumes, it will be removed from the database.
     */
    public function scanStorage()
    {
        Logging::logInformation("Scanning comic storage...");
        $volumeIDsAndPathsFromStorage = self::getVolumeIDsAndPaths();
        $volumesFromDB = $this->volumes->getAll();
        // Reduce $volumesFromDB and $volumeIDsAndPathsFromStorage to arrays of the volumeIDs to prepare for diff.
        $volumeIDsFromDB = array_map(function ($item) {
            return $item["VolumeID"];
        }, $volumesFromDB);
        $volumeIDsFromStorage = array_map(function ($item) {
            return $item["VolumeID"];
        }, $volumeIDsAndPathsFromStorage);

        /*
         * Build lists for notInDB, notInStorage and inDBAndStorage.
         */
        // Subtract $volumeIDsFromDB from $volumeIDsFromStorage.
        $idsNotInDB = array_diff($volumeIDsFromStorage, $volumeIDsFromDB);
        // Subtract $volumeIDsFromStorage from $volumeIDsFromDB.
        $idsNotInStorage = array_diff($volumeIDsFromDB, $volumeIDsFromStorage);
        // Get intersection of $volumeIDsFromStorage and $volumeIDsFromDB.
        $idsInDBAndStorage = array_intersect($volumeIDsFromStorage, $volumeIDsFromDB);

        /*
         * Add paths to lists of IDs.
         */
        $volumesNotInDB = array_filter($volumeIDsAndPathsFromStorage, function ($item) use ($idsNotInDB) {
            // Remove $item if its VolumeID is not in $idsNotInDB.
            return in_array($item["VolumeID"], $idsNotInDB);
        });
        $volumesInDBAndStorage = array_filter($volumeIDsAndPathsFromStorage, function ($item) use ($idsInDBAndStorage) {
            // Remove $item if its VolumeID is not in $idsInDBAndStorage.
            return in_array($item["VolumeID"], $idsInDBAndStorage);
        });

        /*
         * Add volumes from $volumesNotInDB and their issues to the database.
         */
        foreach ($volumesNotInDB as $volume) {
            $this->addVolume($volume["Path"], $volume["VolumeID"]);
        }

        /*
         * Remove volumes of $idsNotInStorage and their issues from database.
         * Remove publisher if it has no more volumes.
         */
        foreach ($idsNotInStorage as $volumeID) {
            $this->removeVolume($volumeID);
        }

        /*
         * Update volumes from $volumesInDBAndStorage and their issues on the database.
         */
        foreach ($volumesInDBAndStorage as $volume) {
            $this->updateVolume($volume["Path"], $volume["VolumeID"]);
        }
        Logging::logInformation("Finished scanning comic storage.");
    }

    /**
     * Add a volume and its issues to the database.
     * Add publisher of volume to database if not exists.
     * @param $relativePath string Path to the volume directory.
     * @param $volumeID string VolumeID from the $VolumeIDFile inside $relativePath.
     */
    public function addVolume($relativePath, $volumeID)
    {
        Logging::logInformation("Adding $relativePath to database...");

        $filesInDirectory = self::getFilesList($relativePath);
        if (!empty($filesInDirectory)) {
            /*
             * Files found. Get volume and volume issues from ComicVine.
             */
            $volume = Volume::get($volumeID);
            $volumeIssuesFromComicVine = VolumeIssue::get($volumeID);
            if (!empty($volume) and !empty($volumeIssuesFromComicVine)) {
                /*
                 * Successfully received volume and volume issues.
                 * Try matching files to issues.
                 */
                $filesAndIssueNumbers = $this->getFilesAndIssueNumbers($filesInDirectory, $relativePath);
                $filenamesAndIssueIDs = $this->matchFilesToIssuesFromComicVine($filesAndIssueNumbers,
                    $volumeIssuesFromComicVine, $relativePath);

                if (!empty($filenamesAndIssueIDs)) {
                    // Matching of at least some issues successful.

                    // Get issue details from ComicVine.
                    $issues = array();
                    foreach ($filenamesAndIssueIDs as $filenameAndIssueID) {
                        $issueFromComicVine = Issue::get($filenameAndIssueID["IssueID"]);
                        if (!empty($issueFromComicVine)) {
                            // Successfully read issue from ComicVine. Add missing details.
                            $issueFromComicVine["IssueLocalPath"] = $filenameAndIssueID["FileName"];
                            $issueFromComicVine["ReadStatus"] = 0;
                            array_push($issues, $issueFromComicVine);
                        } else {
                            // Error reading issue from ComicVine. Error is already logged. Log skipping file.
                            Logging::logError("Error reading from ComicVine API! Skipping $relativePath/" .
                                $filenameAndIssueID["FileName"] . ".");
                        }
                    }

                    if (!empty($issues)) {
                        // Issues are ready for the database.

                        /*
                         * Check if publisher is on the database. Add if not.
                         */
                        $publisherFromDB = $this->publishers->get($volume["PublisherID"]);
                        if (empty($publisherFromDB)) {
                            $publisherFromComicVine = Publisher::get($volume["PublisherID"]);
                            if (!empty($publisherFromComicVine)) {
                                // Successfully read publisher from ComicVine. Add to DB and update image cache.
                                $this->publishers->add($publisherFromComicVine);

                                /*
                                 * Update image on cache.
                                 */
                                $this->imageCache->updatePublisherImage($publisherFromComicVine);
                            } else {
                                // Error reading publisher from ComicVine. Error is already logged. Log skipping of dir.
                                Logging::logError("Error reading from ComicVine API! Skipping $relativePath.");
                                return;
                            }
                        }

                        /*
                        * Add volume to database.
                        */
                        $volume["ReadStatus"] = 0;
                        $volume["VolumeLocalPath"] = $relativePath;
                        $this->volumes->add($volume);

                        /*
                         * Update image on cache.
                         */
                        $this->imageCache->updateVolumeImage($volume);

                        /*
                         * Add issues to database and update image cache.
                         */
                        foreach ($issues as $issue) {
                            $this->addIssue($issue);
                        }

                        Logging::logInformation("Successfully added $relativePath to database.");
                    }
                } else {
                    // Could not match any comic files. Log error.
                    Logging::logError("$relativePath does not contain any matchable comic issues! Skipping directory.");
                }

            } else {
                // Error reading from API. Error is already logged. Log skipping.
                Logging::logError("Error reading from ComicVine API! Skipping $relativePath.");
            }
        } else {
            // Directory does not contain any issues. Log error and skip directory.
            Logging::logError("$relativePath does not contain any comic issues! Skipping directory.");
        }
    }

    /**
     * Remove a volume and its issues from the database.
     * If the publisher has no other volumes, remove it too.
     * @param $volumeID string VolumeID of the volume to remove.
     */
    public function removeVolume($volumeID)
    {
        // Get volume from database.
        $volume = $this->volumes->get($volumeID);
        // Get publisher from database.
        $publisher = $this->publishers->get($volume["PublisherID"]);
        Logging::logWarning("Volume " . $volume["Name"] . " was not found (should have been at " .
            $volume["VolumeLocalPath"] . "). Removing volume and its issues from database.");
        // Get issues of the volume.
        $issues = $this->volumeIssues->getSelection($volumeID);

        // Remove issues from database and image cache.
        foreach ($issues as $issue) {
            $this->removeIssue($issue);
        }

        // Delete volume from image cache.
        $this->imageCache->removeFromCache($volume);
        // Delete volume from database.
        Logging::logWarning("Removing volume " . $volume["VolumeLocalPath"] . " from the database.");
        $this->volumes->remove($volumeID);

        // Check if the publisher has volumes left on the database.
        $publisherVolumes = $this->publisherVolumes->getSelection($volume["PublisherID"]);
        if (empty($publisherVolumes)) {
            // No volumes left for this publisher. Delete.
            Logging::logWarning("Publisher " . $publisher["Name"] . " has no volumes left. Removing from database!");
            $this->publishers->remove($volume["PublisherID"]);
        }   // Else there are other volumes by this publisher.
    }

    /**
     * Update a volume that is already on the database.
     * Update the path to the volume on the database if it has changed.
     * Remove issues from the database if their files can not be found anymore.
     * Add new issues to the database.
     * Update the filenames of issues on the database if they have changed.
     * Remove the volume from the database if it has no issues.
     * Remove the publisher of the volume from the database if it has no volumes.
     * @param $relativePath string Path to the volume directory.
     * @param $volumeID string VolumeID from the $VolumeIDFile inside $relativePath.
     */
    private function updateVolume($relativePath, $volumeID)
    {
        // Get volume from database.
        $volume = $this->volumes->get($volumeID);

        /*
         *Check if the volume path has changed.
         */
        if ($relativePath !== $volume["VolumeLocalPath"]) {
            // Update path on database.
            Logging::logInformation($volume["VolumeLocalPath"] . " was renamed to $relativePath. Updating database.");
            $volume["VolumeLocalPath"] = $relativePath;
            $this->volumes->update($volume);
        }

        /*
         * Check which files are already on the database.
         */

        // Get files from directory.
        $filesInDirectory = self::getFilesList($relativePath);
        // Get list of files and issue numbers.
        $filesAndIssueNumbers = $this->getFilesAndIssueNumbers($filesInDirectory, $relativePath);
        // Get volumeIssues from database.
        $volumeIssues = $this->volumeIssues->getSelection($volumeID);
        // Make an array like array([0] => "someIssueNumber", ...).
        $onDB = array_map(function ($item) {
            return $item["IssueNumber"];
        }, $volumeIssues);

        // Get list of files that are on the database and storage.
        $onDBAndStorage = array_filter($filesAndIssueNumbers, function ($item) use ($onDB) {
            if (ctype_digit($item["IssueNumber"])) {
                // Issue number is an integer. Compare int value to avoid problems with leading 0s.
                return in_array(intval($item["IssueNumber"]), $onDB);
            } else {
                return in_array($item["IssueNumber"], $onDB);
            }
        });

        // Get a list of the issue numbers of the files.
        $onStorage = array_map(function ($item) {
            return $item["IssueNumber"];
        }, $filesAndIssueNumbers);

        // Get a list of the issue numbers that are on the database, but not on the files list.
        $notOnStorage = array_filter($volumeIssues, function ($item) use ($onStorage) {
            if (ctype_digit($item["IssueNumber"])) {
                // Issue number is an integer. Compare int value to avoid problems with leading 0s.
                return !in_array(intval($item["IssueNumber"]), $onStorage);
            } else {
                return !in_array($item["IssueNumber"], $onStorage);
            }
        });

        // Get a list of the issue numbers that are not on the database.
        $notOnDB = array_filter($filesAndIssueNumbers, function ($item) use ($onDB) {
            if (ctype_digit($item["IssueNumber"])) {
                // Issue number is an integer. Compare int value to avoid problems with leading 0s.
                return !in_array(intval($item["IssueNumber"]), $onDB);
            } else {
                return !in_array($item["IssueNumber"], $onDB);
            }
        });

        // Add new issues if any.
        if (!empty($notOnDB)) {
            /*
             * Add issues from notOnDB to database.
             */
            $volumeIssuesFromComicVine = volumeIssue::get($volumeID);
            // Match files to issues from ComicVine.
            $newIssues = $this->matchFilesToIssuesFromComicVine($notOnDB, $volumeIssuesFromComicVine, $relativePath);

            // Add issues to database and image cache.
            $issues = array();
            foreach ($newIssues as $newIssue) {
                $issueFromComicVine = issue::get($newIssue["IssueID"]);
                if (!empty($issueFromComicVine)) {
                    // Successfully read issue from ComicVine. Add missing details.
                    $issueFromComicVine["IssueLocalPath"] = $newIssue["FileName"];
                    $issueFromComicVine["ReadStatus"] = 0;
                    array_push($issues, $issueFromComicVine);
                } else {
                    // Error reading issue from ComicVine. Error is already logged. Log skipping file.
                    Logging::logError("Error reading from ComicVine API! Skipping $relativePath/" .
                        $newIssue["FileName"] . ".");
                }
            }
            if (!empty($issues)) {
                // Issues are ready for database. Add to database and image cache.
                foreach ($issues as $issue) {
                    $this->addIssue($issue);
                }
            }
        }

        // Remove issues that are missing on storage.
        foreach ($notOnStorage as $issue) {
            $this->removeIssue($issue);
        }

        /*
         * Update issues that are on database and storage.
         */
        $issuesToUpdate = $this->matchFilesToIssuesFromDB($onDBAndStorage, $volumeIssues);
        foreach ($issuesToUpdate as $issue) {
            $this->updateIssue($issue["FileName"], $issue["IssueID"]);
        }

        // Check if the volume lost all issues while updating. Delete volume if that is the case.
        $volumeIssues = $this->volumeIssues->getSelection($volumeID);
        if (empty($volumeIssues)) {
            $this->removeVolume($volumeID);
        }

    }

    /**
     * Add issue to the database and image cache.
     * @param $issueToAdd array The issue to add.
     */
    private function addIssue($issueToAdd)
    {
        Logging::logInformation("Adding " . $issueToAdd["IssueLocalPath"] . " to database.");
        $this->issues->add($issueToAdd);

        // Update image on cache.
        $this->imageCache->updateIssueImage($issueToAdd);
    }

    /**
     * Remove issue from database and image cache.
     * @param $issueToRemove array The issue to remove.
     */
    private function removeIssue($issueToRemove)
    {
        // Remove from image cache.
        $this->imageCache->removeFromCache($issueToRemove);
        // Delete from database.
        Logging::logWarning("Removing issue " . $issueToRemove["IssueLocalPath"] . " from database.");
        $this->issues->remove($issueToRemove["IssueID"]);
    }

    /**
     * Update the file name of an issue on the database if it has changed.
     * @param $filename string File name of the issue file.
     * @param $issueID string IssueID of the issue.
     */
    private function updateIssue($filename, $issueID)
    {
        // Get issue from database.
        $issue = $this->issues->get($issueID);
        if ($filename !== $issue["IssueLocalPath"]) {
            // File name has changed. Update issue on database.
            Logging::logInformation($issue["IssueLocalPath"] . " was renamed to $filename. Updating database.");
            $issue["IssueLocalPath"] = $filename;
            $this->issues->update($issue);
        }   // Else paths match. Nothing to do.
    }

    /**
     * Get an array of the file names from $filesList and their issue numbers.
     * @param $filesList array List of the files to get the issue number of.
     * @param $relativePath string Path to the directory containing the files.
     * @return array Array like array(array("FileName" => "someFileName", "IssueNumber" => "someIssueNumber")).
     */
    private function getFilesAndIssueNumbers($filesList, $relativePath)
    {
        // Get issue numbers from file names.
        $filesAndIssueNumbers = array_map(function ($fileName) use ($relativePath) {
            if (strpos($fileName, self::$IssueNumberDelimiter) !== false) {
                // File name contains $IssueNumberDelimiter. Try to get the issue number. Should be right after delimiter.
                $nameOnly = implode(array_slice(explode(".", $fileName), 0, -1));
                $issueNumber = array_pop(explode(self::$IssueNumberDelimiter, $nameOnly));
                return array("FileName" => $fileName, "IssueNumber" => $issueNumber);
            } else {
                // No issue number in filename. Log error and return array with empty issue number.
                Logging::logError("No valid issue number in $relativePath/$fileName! Skipping.");
                return array("FileName" => $fileName, "IssueNumber" => "");
            }
        }, $filesList);
        return $filesAndIssueNumbers;
    }

    /**
     * Match files to issues from the database based on the issue numbers from the file names.
     * @param $filesAndIssueNumbers array List of the file names and their issue numbers.
     * @param $volumeIssuesFromDB array List of the issues of a volume from the database.
     * @return array Array of the file names and their IssueIDs like array(array("FileName" => "someFileName",
     * "IssueID" => "someIssueID"));
     */
    private function matchFilesToIssuesFromDB($filesAndIssueNumbers, $volumeIssuesFromDB)
    {
        // Match files to issueIDs.
        $issues = array();
        foreach ($filesAndIssueNumbers as $fileAndIssueNumber) {
            if ($fileAndIssueNumber["IssueNumber"] !== "") {
                // Try matching to issue number from database.
                foreach ($volumeIssuesFromDB as $volumeIssue) {
                    // Check if both numbers contain only digits.
                    // If they are, compare them as integers to avoid problems with leading 0s.
                    // Else, compare as strings.
                    if (ctype_digit($volumeIssue["IssueNumber"]) && ctype_digit($fileAndIssueNumber["IssueNumber"])) {
                        if (intval($volumeIssue["IssueNumber"]) == intval($fileAndIssueNumber["IssueNumber"])) {
                            $fileAndIssueID = array("FileName" => $fileAndIssueNumber["FileName"],
                                "IssueID" => $volumeIssue["IssueID"]);
                            array_push($issues, $fileAndIssueID);
                            break;
                        }
                    } else {
                        if ($volumeIssue["IssueNumber"] == $fileAndIssueNumber["IssueNumber"]) {
                            $fileAndIssueID = array("FileName" => $fileAndIssueNumber["FileName"],
                                "IssueID" => $volumeIssue["IssueID"]);
                            array_push($issues, $fileAndIssueID);
                            break;
                        }
                    }
                }
            }
        }
        return $issues;
    }

    /**
     * Match files to issues from ComicVine based on the issue numbers from the file names.
     * @param $filesAndIssueNumbers array List of the file names and their issue numbers.
     * @param $volumeIssuesFromComicVine array List of the issues of a volume from ComicVine.
     * @param $relativePath string Path to the volume directory.
     * @return array Array of the file names and their IssueIDs like array(array("FileName" => "someFileName",
     * "IssueID" => "someIssueID"));
     */
    private function matchFilesToIssuesFromComicVine($filesAndIssueNumbers, $volumeIssuesFromComicVine, $relativePath)
    {
        // Match files to issueIDs.
        $filesAndIssueIDs = array();
        foreach ($filesAndIssueNumbers as $fileAndIssueNumber) {
            if ($fileAndIssueNumber["IssueNumber"] !== "") {
                // Try matching to issue number from ComicVine.
                $fileAndIssueID = array();

                foreach ($volumeIssuesFromComicVine["Issues"] as $volumeIssue) {

                    // Check if both numbers contain only digits.
                    // If they are, compare them as integers to avoid problems with leading 0s.
                    // Else, compare as strings.
                    if (ctype_digit($volumeIssue["IssueNumber"]) && ctype_digit($fileAndIssueNumber["IssueNumber"])) {
                        if (intval($volumeIssue["IssueNumber"]) == intval($fileAndIssueNumber["IssueNumber"])) {
                            $fileAndIssueID = array("FileName" => $fileAndIssueNumber["FileName"],
                                "IssueID" => $volumeIssue["IssueID"]);
                            array_push($filesAndIssueIDs, $fileAndIssueID);
                            break;
                        }
                    } else {
                        if ($volumeIssue["IssueNumber"] == $fileAndIssueNumber["IssueNumber"]) {
                            $fileAndIssueID = array("FileName" => $fileAndIssueNumber["FileName"],
                                "IssueID" => $volumeIssue["IssueID"]);
                            array_push($filesAndIssueIDs, $fileAndIssueID);
                            break;
                        }
                    }
                }

                if (empty($fileAndIssueID)) {
                    // Could not match file to issue. Log error.
                    Logging::logError("Could not match $relativePath/" . $fileAndIssueNumber["FileName"] .
                        " to any issue from ComicVine! Skipping file.");
                }
            }
        }
        return $filesAndIssueIDs;
    }

    /**
     * Get a list of the volume directories and their volumeIDs found in $StoragePath.
     * @return array An array of arrays like array(array("Path" => "somePath", "VolumeID" => "someID") ...).
     */
    private static function getVolumeIDsAndPaths()
    {
        $directories = scandir(self::$StoragePath);   // List of all files and directories of $StoragePath (non-recursive).
        $directories = array_diff($directories, self::$IgnoredFilesAndDirs);  // Remove ignored files and directories from list.
        // Remove files and links from the list.
        $directories = array_filter($directories, function ($item) {
            return is_dir(self::$StoragePath . "/" . $item);
        });

        $volumeIDsAndPaths = array();   // Array to return from the function.

        // Fill array.
        foreach ($directories as $directory) {
            $relativeFilePath = $directory; // Relative path to the directory.
            $absoluteFilePath = self::$StoragePath . "/" . $directory;  // Absolute path to the directory.

            // Check if directory contains a $VolumeIDFile.
            $relativeVolumeIDFilePath = $relativeFilePath . "/" . self::$VolumeIDFile;
            $absoluteVolumeIDFilePath = $absoluteFilePath . "/" . self::$VolumeIDFile;
            if (is_file($absoluteVolumeIDFilePath)) {
                // Check if $VolumeIDFile contains a VolumeID.
                $fileContent = parse_ini_file($absoluteVolumeIDFilePath, true);
                if ($fileContent != false) {
                    // File successfully parsed. Check if it contains a VolumeID.
                    $volumeID = $fileContent["Volume"]["ID"];
                    if (!empty($volumeID)) {
                        // VolumeID successfully read. Add to $volumeIDsAndPaths.
                        array_push($volumeIDsAndPaths, array("VolumeID" => $volumeID, "Path" => $relativeFilePath));
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
        }

        return $volumeIDsAndPaths;
    }

    /**
     * Get the list of file names inside $volumePath.
     * Checks for each element inside $volumePath if it actually is a file (and not a link or directory).
     * @param $relativeVolumePath string Path to get the file names from.
     * @return array Array of file names inside $volumePath. Empty if no files inside directory.
     */
    private static function getFilesList($relativeVolumePath)
    {
        // Get the list of files inside $volumePath.
        $directoryContent = scandir(self::$StoragePath . "/$relativeVolumePath");   // Content of the directory.
        $directoryWithoutIgnored = array_diff($directoryContent, self::$IgnoredFilesAndDirsVolume); // Without ignored.
        $filesInDirectory = // Only files contained in $directoryWithoutIgnored.
            array_filter($directoryWithoutIgnored, function ($item) use ($relativeVolumePath) {
                return is_file(self::$StoragePath . "/$relativeVolumePath/$item");
            });
        return $filesInDirectory;
    }

    /*
     * Getters.
     */

    public static function getStoragePath(): string
    {
        return self::$StoragePath;
    }

}