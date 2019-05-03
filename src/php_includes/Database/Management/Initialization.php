<?php
/**
 * Created by ahahn94
 * on 03.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Management/Connection.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Logging.php";

/**
 * Class Initialization
 * Handles database initialization.
 */
class Initialization
{

    private static $TableList = array("Issues", "Publishers", "Volumes"); // List of the required tables.
    private static $SQLScriptPath = "/var/www/html/php_includes/Database/Management/tables.sql"; // Path to the init script.

    /**
     * Check if the database tables exist.
     * If not, trigger creation of the tables.
     */
    public static function check()
    {
        Logging::logInformation("Checking database initialization...");
        $not_initialized = true; // Initialization state of the database. Assume uninitialized at start.
        $connection = Connection::getInstance();
        $statement = $connection->prepare("SHOW TABLES FROM ComicLib"); // Grab table metadata.
        try {
            $statement->execute();
            // If metadata for at least one table of the database ComicLib was found, continue. Else, initialize tables.
            if ($statement->rowCount() != 0) {
                $not_initialized = false; // Assume initialized. If at least one table can not be found, it will change to true.
                $tables = $statement->fetchAll(PDO::FETCH_ASSOC);
                $tables = array_column($tables, "Tables_in_ComicLib");
                // Check if all tables were found.
                foreach (self::$TableList as $table) {
                    if (!in_array($table, $tables)) {
                        $not_initialized = true;
                    }
                }
            }
            if ($not_initialized) {
                self::initialize();
            } else {
                Logging::logInformation("Database is already initialized.");
            }
        } catch (Exception $e) {
            // Error handling if error while reading from database.
            Logging::logError("Could not check initialization:");
            Logging::logError($e->getMessage());
            print ($e->getMessage());
        }

    }

    /**
     * Initialize the database with the tables needed by ComicLib.
     */
    private static function initialize()
    {
        $connection = Connection::getInstance();
        Logging::logInformation("Initializing database...");
        $script = file_get_contents(self::$SQLScriptPath); // Import the initialization script.
        if ($script === false) {
            // Log error if loading script failed.
            Logging::logError("Could not load initialization script!");
            Logging::logError("Initializing the database failed!");
        } else {
            $statement = $connection->prepare($script);
            try {
                $statement->execute();
                Logging::logInformation("Database successfully initialized.");
            } catch (Exception $e) {
                // Error handling if error while initializing database.
                Logging::logError("Could not initialize database:");
                Logging::logError($e->getMessage());
                print ($e->getMessage());
            }
        }
    }
}