<?php
/**
 * Created by ahahn94
 * on 03.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Management/Connection.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Management/DefaultDatasets.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Logging/Logging.php";

/**
 * Class Initialization
 * Handles database initialization.
 */
class Initialization
{

    private static $TableList = array("Issues", "Publishers", "Volumes", "UserGroups", "Users", "ReadStatus"); // List of the required tables.
    private static $ViewList = array("VolumeIssueCount", "PublisherVolumes", "VolumeIssues", "VolumeReadStatus",
        "IssueReadStatus"); // List of the required views.
    private static $TriggersList = array("CreateReadStatusAfterIssueInsert", "CreateReadStatusAfterUserInsert"); // List of the required triggers.
    private static $TableScriptPath = "/var/www/html/php_includes/Database/Management/tables.sql"; // Path to the tables init script.
    private static $ViewScriptPath = "/var/www/html/php_includes/Database/Management/views.sql"; // Path to the views init script.
    private static $TriggerScriptsPath = "/var/www/html/php_includes/Database/Management/"; // Path to the trigger init scripts.
    private static $TriggerInitScripts = array("trigger1.sql", "trigger2.sql"); // The triggers have to be placed in separate files.

    /**
     * Check if the database tables and views exist.
     * If not, trigger creation of the tables and/or views.
     * Check if the default datasets exist.
     * If not, trigger creation of the datasets.
     */
    public static function check()
    {
        while (($connection = Connection::getInstance()) === null) {
            sleep(1);
        }

        $tablesNotInitialized = true; // Initialization state of the tables. Assume uninitialized at start.
        $viewsNotInitialized = true; // Initialization state of the views. Assume uninitialized at start.
        $statement = $connection->prepare("SHOW TABLES FROM ComicLib"); // Grab table metadata.
        try {
            $statement->execute();
            // If metadata for at least one table of the database ComicLib was found, continue. Else, initialize tables.
            if ($statement->rowCount() != 0) {
                $tablesNotInitialized = false; // Assume initialized. If at least one table can not be found, it will change to true.
                $viewsNotInitialized = false; // Assume initialized. If at least one view can not be found, it will change to true.
                $tables = $statement->fetchAll(PDO::FETCH_ASSOC);
                $tables = array_column($tables, "Tables_in_ComicLib");

                // Check if all tables were found.
                foreach (self::$TableList as $table) {
                    if (!in_array($table, $tables)) {
                        $tablesNotInitialized = true;
                    }
                }
                // Check if all triggers were found.
                $statement = $connection->prepare("SHOW TRIGGERS FROM ComicLib"); // Grab trigger metadata.
                $statement->execute();
                if ($statement->rowCount() == 0) $tablesNotInitialized = true; // Too few triggers.
                else {
                    $triggers = $statement->fetchAll(PDO::FETCH_ASSOC);
                    $triggers = array_column($triggers, "Trigger");
                    foreach (self::$TriggersList as $trigger) {
                        if (!in_array($trigger, $triggers)) {
                            $tablesNotInitialized = true;
                        }
                    }
                }
                // If tables are already initialized, the views may be missing. Otherwise, the views are missing for sure.
                if ($tablesNotInitialized == false) {
                    // Check if all views were found.
                    foreach (self::$ViewList as $view) {
                        if (!in_array($view, $tables)) {
                            $viewsNotInitialized = true;
                        }
                    }
                } else {
                    // No tables -> no views.
                    $viewsNotInitialized = true;
                }
            }
            if ($tablesNotInitialized or $viewsNotInitialized) {
                if ($tablesNotInitialized) {
                    // Neither tables nor views are already initialized (as views depend on tables). Init both.
                    self::initialize(true);
                }
                if (!$tablesNotInitialized and $viewsNotInitialized) {
                    // Tables are already initialized, but views are not. Init views.
                    self::initialize(false);
                }
            }
        } catch (Exception $e) {
            // Error handling if error while reading from database.
            Logging::logError("Could not check database initialization:");
            Logging::logError($e->getMessage());
            print ($e->getMessage());
        }

        // Check initialization of the default datasets.
        $defaultDatasets = new DefaultDatasets();
        $defaultDatasets->checkInitialization();

    }

    /**
     * Initialize the database with the tables and views needed by ComicLib.
     * @param $initTables boolean Initialize the tables or only the views?
     */
    private static function initialize($initTables)
    {
        Logging::logInformation("Initializing database...");
        $errorMessage = "Initializing database failed!";    // Message to log in case of an error.
        if ($initTables) {
            // If the tables have to be initialized, the views do as well.
            $initTablesAndTriggers = 0;
            $initTablesAndTriggers += self::runScript(self::$TableScriptPath, "tables");
            foreach (self::$TriggerInitScripts as $triggerInitScript) {
                $initTablesAndTriggers += self::runScript(self::$TriggerScriptsPath . $triggerInitScript,
                    "triggers");
            }
            if ($initTablesAndTriggers == 0) {
                // If script was run successfully (returns 0), init views.
                if (self::runScript(self::$ViewScriptPath, "views") == 0) {
                    // If script was run successfully, log success.
                    Logging::logInformation("Database successfully initialized.");
                } else {
                    Logging::logError($errorMessage);
                    print ($errorMessage . "<br>");
                }
            } else {
                Logging::logError($errorMessage);
                print ($errorMessage . "<br>");
            }
        } else {
            // Tables are already initialized. Init views.
            Logging::logInformation("Tables are already initialized. Initializing views...");
            if (self::runScript(self::$ViewScriptPath, "views") == 0) {
                // Script was run successfully. Log success.
                Logging::logInformation("Database successfully initialized.");
            } else {
                Logging::logError($errorMessage);
                print ($errorMessage . "<br>");
            }
        }
    }

    /**
     * Run an initialization script.
     * @param $scriptPath string Path to the .sql file.
     * @param $objectType string Either tables or views. Needed for logging.
     * @return int 0 if ok. 1 if not.
     */
    private static function runScript($scriptPath, $objectType)
    {
        $connection = Connection::getInstance();
        $script = file_get_contents($scriptPath); // Import the initialization script.
        if ($script === false) {
            // Log error if loading script failed.
            $errorMessage = "Could not load " . $objectType . " initialization script!";
            Logging::logError($errorMessage);
            print ($errorMessage . "<br>");
            return 1;
        } else {
            $statement = $connection->prepare($script);
            try {
                $statement->execute();
                Logging::logInformation("Database " . $objectType . " successfully initialized.");
                return 0;
            } catch (Exception $e) {
                // Error handling if error while running script.
                $errorMessage = "Could not initialize database " . $objectType . ":";
                Logging::logError($errorMessage);
                print ($errorMessage . "<br>");
                Logging::logError($e->getMessage());
                print ($e->getMessage() . "<br>");
                return 1;
            }
        }
    }
}