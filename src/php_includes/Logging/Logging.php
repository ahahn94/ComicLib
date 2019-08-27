<?php
/**
 * Created by ahahn94
 * on 03.05.19
 */

/**
 * Class Logging
 * Handles logging of messages.
 */
class Logging
{

    private static $LogFile = "/var/www/html/log.txt"; // Path to log file.

    // Headers for different types of messages.
    private static $ErrorHeader = "[ERR] ";
    private static $InformationHeader = "[INF] ";
    private static $WarningHeader = "[WAR] ";

    /**
     * Log message of type "error" to log file.
     * @param $message string Message to write to the log file.
     */
    public static function logError($message)
    {
        error_log(self::generateHeader("error") . $message . "\n", 3, self::$LogFile);
    }

    /**
     * Log message of type "information" to log file.
     * @param $message string Message to write to the log file.
     */
    public static function logInformation($message)
    {
        error_log(self::generateHeader("information") . $message . "\n", 3, self::$LogFile);
    }

    /**
     * Log message of type "warning" to log file.
     * @param $message string Message to write to the log file.
     */
    public static function logWarning($message)
    {
        error_log(self::generateHeader("warning") . $message . "\n", 3, self::$LogFile);
    }

    /**
     * Generate a header like "[TYPE] 2019-01-01 HH:MM:SS ".
     * @param $messageType string Type of the message to log.
     * @return string The generated header.
     */
    private static function generateHeader($messageType)
    {
        $header = "";
        if ($messageType == "error") {
            $header = self::$ErrorHeader;
        }
        if ($messageType == "information") {
            $header = self::$InformationHeader;
        }
        if ($messageType == "warning") {
            $header = self::$WarningHeader;
        }
        $header = $header . date("Y-m-d H:i:s") . " ";
        return $header;
    }
}