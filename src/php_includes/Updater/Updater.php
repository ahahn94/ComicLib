<?php
/**
 * Created by ahahn94
 * on 17.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Storage/StorageManager.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Caching/ImageCache.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Logging/Logging.php";

/**
 * Class Updater
 * Implements the control functions to the update mechanics of the database and image cache.
 */
class Updater
{

    // Path to the lock file. The lock file assures that only one instance of the Updater will run at a time.
    private static $lockFile = "/var/www/html/updater.lock";

    /**
     * Updater constructor.
     */
    public function __construct()
    {
        /**
         * Nothing to do here.
         */
    }

    /**
     * Check if the updater is running.
     * @return boolean True if running updates, else false.
     */
    public static function updaterRunning()
    {
        // Check if lock file is locked. If try to acquire a lock on the file blocks, the updater is running.
        // To avoid interference with the update process, use a non-blocking, non-exclusive call and close the file
        // immediately after flock.
        $file = fopen(self::$lockFile, "r");
        $wouldBlock = null;
        flock($file, LOCK_SH | LOCK_NB, $wouldBlock);
        fclose($file);
        return $wouldBlock;
    }

    /**
     * Update database and image cache based on the whole /storage directory.
     */
    public function updateAll()
    {
        $file = fopen(self::$lockFile, "r+");
        if ($file !== false) {
            // Successfully opened file.
            if (flock($file, LOCK_EX | LOCK_NB)) {
                // Successfully acquired lock -> no other updates running in background.
                // Start update.
                Logging::logInformation("Starting update of database and cache...");
                $storageManager = new StorageManager();
                $storageManager->scanStorage();
                $imageCache = new ImageCache();
                $imageCache->updateCache();
                Logging::logInformation("Update of database and cache finished.");
                fclose($file);
            }   // Else another update process is already running.
        } else {
            // Unable to open lock file. Log error.
            Logging::logError("Could not open lock file " . self::$lockFile . "! Unable to start update of database and cache!");
        }
    }

}