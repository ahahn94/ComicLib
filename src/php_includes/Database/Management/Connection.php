<?php
/**
 * Created by ahahn94
 * on 03.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Configuration/Configuration.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Management/Initialization.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Logging/Logging.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Exceptions/EmptyOrUnreadableConfigurationException.php";

/**
 * Class Connection
 * Singleton class that implements the connection to the database via PDO.
 */
class Connection
{
    // Singleton connection object.
    private static ?PDO $instance = null;

    private static array $pdoOptions;
    private static string $dsn;
    private static string $dbUsername;
    private static string $dbPassword;

    /**
     * Get the PDO connection.
     * Will initialize the connection if not already initialized.
     * Will initialize the database tables if not already initialized.
     * @return PDO connection.
     */
    public static function getInstance(): ?PDO
    {
        // Check if connection is not yet initialized.
        if (!isset(self::$instance)) {
            return self::initialize();
        } else {
            return self::$instance;
        }
    }

    private static function initialize(): ?PDO
    {
        try {
            self::configurePdoOptions();
            self::$instance = new PDO(self::$dsn, self::$dbUsername, self::$dbPassword, self::$pdoOptions);
            return self::$instance;
        } catch (Exception $e) {
            switch (get_class($e)) {
                case EmptyOrUnreadableConfigurationException::class:
                    Logging::logError("Connecting to the database failed! Couldn't read configuration file or it is empty.");
                    return null;
                default:
                    Logging::logError("Could not connect to database:");
                    Logging::logError($e->getMessage());
                    print ($e->getMessage());
                    return null;
            }
        }
    }

    /**
     * @throws EmptyOrUnreadableConfigurationException
     */
    private static function configurePdoOptions(): void
    {
        if (self::optionsAlreadyConfigured()) return;

        $dbConfig = self::getDbConfig();

        self::$pdoOptions = array(
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,        // Make SQL-errors raise exceptions.
            PDO::MYSQL_ATTR_INIT_COMMAND => "Set Names utf8"    // Force utf8 charset.
        );

        self::$dsn = "mysql:host={$dbConfig["MYSQL_HOST"]};dbname={$dbConfig["MYSQL_DATABASE"]};";
        self::$dbUsername = $dbConfig["MYSQL_USER"];
        self::$dbPassword = $dbConfig["MYSQL_PASSWORD"];

    }

    /**
     * @throws EmptyOrUnreadableConfigurationException
     */
    private static function getDbConfig(): array
    {
        $config = Configuration::getConfiguration(); // Get the database connection config.
        if (empty($config)) {
            throw new EmptyOrUnreadableConfigurationException();
        }
        return $config["Database"];
    }

    private static function optionsAlreadyConfigured(): bool {
        return !empty(self::$pdoOptions) && !empty(self::$dsn) && !empty(self::$dbUsername) && !empty(self::$dbPassword);
    }
}

