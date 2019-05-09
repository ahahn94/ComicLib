<?php
/**
 * Created by ahahn94
 * on 06.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Configuration/Configuration.php";

/**
 * Class APIConfiguration
 * Class that handles the configuration of the API parameters.
 */
class APIConfiguration
{
    // URL to the API root.
    private static $APIRootURL = "https://comicvine.gamespot.com/api/";

    // Strings containing the API config.
    private static $APIKey = null;  // API authentication key.
    private static $IssuePrefix = "4000-";  // Prefix to the issue IDs.
    private static $PublisherPrefix = "4010-";  // Prefix to the publisher IDs.
    private static $VolumePrefix = "4050-"; // Prefix to the volume IDs.
    private static $DataFormat = "json";    // Format of the data returned by the api.

    // HTTP GET keys.
    private static $FormatParam = "format=";    // Key for the data format specifier.
    private static $ApiKeyParam = "api_key=";   // Key for the api key specifier.

    private static $getAKeyReminder = "You have to get a valid API key from https://comicvine.gamespot.com/api/ for ComicLib to work";

    /**
     * Assemble the URL for an API request.
     * @param $url string URL of the resource item to request.
     * @param $options string String containing the options to append to the end of the request URL.
     * @return string Input URL + authentication and data format parameters + $options.
     */
    public static function assembleURL($url, $options)
    {
        return $url . "?" . self::$ApiKeyParam . self::getAPIKey() . "&" . self::$FormatParam . self::$DataFormat . $options;
    }

    /**
     * Get the API key.
     * Read the API key from the config if not yet set.
     * @return string API key or null if error reading config.
     */
    private static function getAPIKey()
    {
        if (!isset(self::$APIKey)) {
            // $APIKey is not set. Read from config.
            $config = Configuration::getConfiguration();
            Logging::logInformation("Reading ComicVine API key");

            if (empty($config)) {
                // Check if error reading API key.
                // If error reading config, log and print error message.
                $errorMessage = "Could not read API key from config file!";
                Logging::logError($errorMessage);
                print($errorMessage . "<br>");
                return null;
            }

            if (!empty($config = $config["ComicVineAPI"])) {
                // Reduce config to ComicVine API part.
                if (isset($config["API_KEY"]) and $config["API_KEY"] != "") {
                    // Check if key is presumably valid.
                    if ($config["API_KEY"] == "ReplaceThisWithYourAPIKeyFromhttps://comicvine.gamespot.com/api/") {
                        // The default value of API_KEY was not yet replaced by a valid key. Log and print error.
                        $errorMessage = "The API_KEY key-value-pair inside " . Configuration::getConfigPath() . " is still the default value!";
                        Logging::logError($errorMessage);
                        print($errorMessage . "<br>");
                        Logging::logError(self::$getAKeyReminder);
                        print(self::$getAKeyReminder . "<br>");
                        return null;
                    }
                } else {
                    // If API_KEY is not set, log and print error message.
                    $errorMessage = "The API_KEY key-value-pair inside " . Configuration::getConfigPath() . " is not set!";
                    Logging::logError($errorMessage);
                    print($errorMessage . "<br>");
                    Logging::logError(self::$getAKeyReminder);
                    print(self::$getAKeyReminder . "<br>");
                    return null;
                }
            } else {
                // ComicVineAPI section is not set. Log and print error.
                $errorMessage = "The ComicVineAPI section is missing in your " . Configuration::getConfigPath() . "!";
                Logging::logError($errorMessage);
                print($errorMessage . "<br>");
                Logging::logError(self::$getAKeyReminder);
                print(self::$getAKeyReminder . "<br>");
                return null;
            }
            self::$APIKey = $config["API_KEY"];
        }
        return self::$APIKey;
    }

    /*
     * Getter functions.
     */

    public static function getAPIRootURL(): string
    {
        return self::$APIRootURL;
    }

    public static function getIssuePrefix(): string
    {
        return self::$IssuePrefix;
    }

    public static function getPublisherPrefix(): string
    {
        return self::$PublisherPrefix;
    }

    public static function getVolumePrefix(): string
    {
        return self::$VolumePrefix;
    }

}