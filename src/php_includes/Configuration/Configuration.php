<?php
/**
 * Created by ahahn94
 * on 06.05.19
 */

/**
 * Class Configuration
 * Singleton class that handles the data from the configuration file.
 */
class Configuration
{

    // Path to the file with the configuration data.
    private static $ConfigPath = "/var/www/ComicLibConfig/config.ini";

    // Singleton configuration object.
    private static $config = null;

    /**
     * Get the configuration array.
     * @return array Array with the configuration key->value pair. Empty array if error.
     */
    public static function getConfiguration()
    {
        if (!isset(self::$config)) {
            self::$config = self::readConfig();
        }
        return self::$config;
    }

    /**
     * Read the config from the config file.
     * @return array Array of Key->Value pairs. Empty array if error.
     */
    private static function readConfig()
    {
        Logging::logInformation("Reading configuration from " . self::$ConfigPath . ".");
        $read_config = parse_ini_file(self::$ConfigPath, true);
        if ($read_config === false) {
            // Error handling if error while reading config.
            Logging::logError("Could not read configuration file!");
            return array();
        }
        return $read_config;
    }

    /*
     * Getter functions.
     */

    public static function getConfigPath()
    {
        return self::$ConfigPath;
    }

}