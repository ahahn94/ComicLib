<?php
/**
 * Created by ahahn94
 * on 16.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Issues.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Publishers.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Volumes.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicVineAPI/Resources/Image.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Logging/Logging.php";

/**
 * Class ImageCache
 * Implements the functions for managing the image cache.
 */
class ImageCache
{
    /*
     * Use only one instance of every data repo needed.
     */
    private $IssuesRepo = null;
    private $PublishersRepo = null;
    private $VolumesRepo = null;
    private $ComicVineImageRepo = null;

    private static $ImageCacheAbsolutePath = "/var/www/html/cache/images/";
    private static $ImageCachePath = "/cache/images/";

    /**
     * ImageCache constructor.
     */
    public function __construct()
    {
        $this->IssuesRepo = new Issues();
        $this->PublishersRepo = new Publishers();
        $this->VolumesRepo = new Volumes();
        $this->ComicVineImageRepo = new Image();
    }

    /**
     * Update cache to include every image from Issues, Publishers and Volumes.
     * The images are already downloaded right after the dataset is added to the database.
     * Running this function after the batch of updates to the database will make sure that
     * images that went missing (e.g. if the user deleted the image cache but not the database)
     * will be re-cached.
     */
    public function updateCache()
    {
        Logging::logInformation("Starting update of image cache...");

        /*
         * Get Publishers, Issues and Volumes from the database.
         */

        $issues = $this->IssuesRepo->getAll();
        $publishers = $this->PublishersRepo->getAll();
        $volumes = $this->VolumesRepo->getAll();

        /*
         * Check if image files are already cached.
         * Download image to cache and update database entry if not.
         */
        foreach ($issues as $issue) {
            self::updateIssueImage($issue);
        }
        foreach ($publishers as $publisher) {
            self::updatePublisherImage($publisher);
        }
        foreach ($volumes as $volume) {
            self::updateVolumeImage($volume);
        }

        Logging::logInformation("Update of image cache finished.");
    }

    /**
     * Check if the image of the provided $issue is already cached.
     * Download to cache and update database if not.
     * @param $issue array Issue of which to check and update the image.
     */
    public function updateIssueImage($issue)
    {
        if (!$this->isCached($issue["ImageFileName"])) {
            // Image file is not cached. Add to cache.
            $extension = array_pop(explode(".", basename($issue["ImageURL"])));
            $fileName = APIConfiguration::getIssuePrefix() . $issue["IssueID"] . ".$extension";
            Logging::logInformation(self::$ImageCacheAbsolutePath . "$fileName is not cached. Adding to cache...");
            $addedSuccessfully = $this->addToCache($fileName, $issue["ImageURL"]);
            if ($addedSuccessfully) {
                // File was successfully cached. Add $fileName to $issue and update database.
                Logging::logInformation(self::$ImageCacheAbsolutePath . "$fileName successfully added to cache.");
                $issue["ImageFileName"] = $fileName;
                $this->IssuesRepo->update($issue);
            } // Else error writing file. Error is already logged.
        }
    }

    /**
     * Check if the image of the provided $publisher is already cached.
     * Download to cache and update database if not.
     * @param $publisher array Publisher of which to check and update the image.
     */
    public function updatePublisherImage($publisher)
    {
        if (!$this->isCached($publisher["ImageFileName"])) {
            // Image file is not cached. Add to cache.
            $extension = array_pop(explode(".", basename($publisher["ImageURL"])));
            $fileName = APIConfiguration::getPublisherPrefix() . $publisher["PublisherID"] . ".$extension";
            Logging::logInformation(self::$ImageCacheAbsolutePath . "$fileName is not cached. Adding to cache...");
            $addedSuccessfully = $this->addToCache($fileName, $publisher["ImageURL"]);
            if ($addedSuccessfully) {
                // File was successfully cached. Add $fileName to $publisher and update database.
                Logging::logInformation(self::$ImageCacheAbsolutePath . "$fileName successfully added to cache.");
                $publisher["ImageFileName"] = $fileName;
                $this->PublishersRepo->update($publisher);
            } // Else error writing file. Error is already logged.
        }
    }

    /**
     * Check if the image of the provided $volume is already cached.
     * Download to cache and update database if not.
     * @param $volume array Volume of which to check and update the image.
     */
    public function updateVolumeImage($volume)
    {
        if (!$this->isCached($volume["ImageFileName"])) {
            // Image file is not cached. Add to cache.
            $extension = array_pop(explode(".", basename($volume["ImageURL"])));
            $fileName = APIConfiguration::getVolumePrefix() . $volume["VolumeID"] . ".$extension";
            Logging::logInformation(self::$ImageCacheAbsolutePath . "$fileName is not cached. Adding to cache...");
            $addedSuccessfully = $this->addToCache($fileName, $volume["ImageURL"]);
            if ($addedSuccessfully) {
                // File was successfully cached. Add $fileName to $volume and update database.
                Logging::logInformation(self::$ImageCacheAbsolutePath . "$fileName successfully added to cache.");
                $volume["ImageFileName"] = $fileName;
                $this->VolumesRepo->update($volume);
            } // Else error writing file. Error is already logged.
        }
    }

    /**
     * Check if the image file specified by $fileName exists inside the cache directory.
     * @param $fileName string Name of the image file.
     * @return boolean True if the file exists, else false.
     */
    private function isCached($fileName)
    {
        // Check if $fileName is empty (which means that the file has not been cached until now).
        if (empty($fileName)) return false;
        // Check if the file exists.
        return is_file(self::$ImageCacheAbsolutePath . $fileName);
    }

    /**
     * Download and add an image to the cache.
     * @param $fileName string Name of the file to create.
     * @param $url string URL to the image file on the ComicVine API.
     * @return bool true if added successfully, else false.
     */
    private function addToCache($fileName, $url)
    {
        $imageData = $this->ComicVineImageRepo::get($url);
        if (!empty($imageData)) {
            // Successfully received image data. Write to file.
            $imageFile = fopen(self::$ImageCacheAbsolutePath . $fileName, "w+");
            if ($imageFile !== false) {
                // Successfully opened file for writing. Continue.
                $result = fwrite($imageFile, $imageData);
                fclose($imageFile);
                if ($result !== false) {
                    // No errors during writing. Return true.
                    return true;
                } else {
                    // Errors during writing of the file. Log error and return false.
                    Logging::logError("Error writing " . self::$ImageCacheAbsolutePath . $fileName . "! Skipping file.");
                    return false;
                }
            } else {
                // Could not open file for writing. Log error and return false.
                Logging::logError("Could not open or create " . self::$ImageCacheAbsolutePath . $fileName .
                    " for writing! Skipping file.");
                return false;
            }
        }
        // No image data received. Error was already logged in ComicVineAPI/Management/APICall. Return false.
        return false;
    }

    /**
     * Remove a file from the image cache.
     * @param $dataset array Dataset to remove the image file of.
     */
    public function removeFromCache($dataset)
    {
        // Delete file.
        if (unlink(self::$ImageCacheAbsolutePath . $dataset["ImageFileName"])) {
            Logging::logInformation("Successfully deleted " . $dataset["ImageFileName"] . " from image cache.");
        } else {
            Logging::logError("Could not delete " . $dataset["ImageFileName"] . " from image cache!");
        }
    }

    /*
     * Getters.
     */

    public static function getImageCachePath(): string
    {
        return self::$ImageCachePath;
    }

}