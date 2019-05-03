<?php
/**
 * Created by ahahn94
 * on 03.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Management/Initialization.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Logging.php";

/**
 * Class Connection
 * Singleton class that implements the connection to the database via PDO.
 */
class Connection
{
    // Path to the file with the connection details.
    private static $ConfigPath = "/var/www/config.ini";

    // Singleton connection object.
    private static $instance = null;

    /**
     * Get the PDO connection.
     * Will initialize the connection if not already initialized.
     * Will initialize the database tables if not already initialized.
     * @return PDO connection.
     */
    public static function getInstance()
    {
        // Check if connection is not yet initialized.
        if (!isset(self::$instance)) {
            Logging::logInformation("Start connecting to database...");
            $config = self::getConfig(); // Get the database connection config.
            if (!empty($config)) {
                // If reading config was successful, continue.
                $pdo_options[PDO::ATTR_ERRMODE] = PDO::ERRMODE_EXCEPTION; // Make SQL-errors raise exceptions.
                $pdo_options[PDO::MYSQL_ATTR_INIT_COMMAND] = "Set Names utf8"; // Force utf8 charset.
                Logging::logInformation("Connecting to the database.");
                try {
                    self::$instance = new PDO("mysql:host=" . $config["MYSQL_HOST"] . ";dbname=" . $config["MYSQL_DATABASE"] .
                        ";", $config["MYSQL_USER"], $config["MYSQL_PASSWORD"], $pdo_options);
                    Logging::logInformation("Connected to database.");
                    Initialization::check();
                } catch (Exception $e) {
                    // Error handling if error while connecting to database.
                    Logging::logError("Could not connect to database:");
                    Logging::logError($e->getMessage());
                    print ($e->getMessage());
                    return self::$instance;
                }
            } else {
                Logging::logError("Connecting to the database failed!");
            }
        }
        return self::$instance;
    }

    /**
     * Get the database config from the config file.
     * @return array Array of Key->Value pairs. Empty array if error.
     */
    private static function getConfig()
    {
        Logging::logInformation("Reading configuration for connection.");
        $config = parse_ini_file(self::$ConfigPath, true);
        if ($config === false) {
            // Error handling if error while reading config.
            Logging::logError("Could not read configuration file!");
            return array();
        }
        return $config["Database"];
    }
}