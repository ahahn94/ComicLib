<?php
/**
 * Created by ahahn94
 * on 26.05.19
 */

/**
 * Class Generics
 * Implements functions to send generic answers to API calls.
 */
class APIGenerics
{

    private static $ContentTypeJSON = "Content-Type: application/json";

    // List of the HTTP response codes and their messages.
    private static $ResponseCodeMessages = array(200 => "OK", 400 => "Bad Request", 401 => "Unauthorized",
        404 => "Not Found", 405 => "Method Not Allowed");

    private static $APIPathV1 = "/api/v1/";     // Web path to the resources of version 1 of the API.

    /**
     * Send an answer to an API request.
     * @param $headers array List of the custom headers to send.
     * @param $body array Array of the body to send.
     * @param $responseCode string HTTP response code.
     */
    public static function sendAnswer($headers, $body, $responseCode)
    {
        // Set response code.
        http_response_code($responseCode);
        // Set headers.
        foreach ($headers as $header) {
            header($header);
        }
        // Get status part of the body.
        $status = self::responseCodeToArray($responseCode);

        // If body is empty, use null instead.
        if (empty($body)) $body = null;

            // Set content.
            $responseBody = json_encode(array_merge($status, array("Content" => $body)));
        print($responseBody);
    }

    /**
     * Get the HTTP request method of the current request.
     * @return string Name of the request method.
     */
    public static function getRequestMethod(): string
    {
        return $_SERVER["REQUEST_METHOD"];
    }

    /**
     * Send a 404 Not Found response.
     */
    public static function sendNotFound()
    {
        $responseCode = 404;
        $headers = array(self::$ContentTypeJSON);
        self::sendAnswer($headers, array(), $responseCode);
    }

    /**
     * Send a 405 Method Not Allowed response.
     */
    public static function sendMethodNotAllowed()
    {
        $responseCode = 405;
        $headers = array(self::$ContentTypeJSON);
        self::sendAnswer($headers, array(), $responseCode);
    }

    /**
     * Send a 401 Unauthorized response.
     */
    public static function sendUnauthorized()
    {
        $responseCode = 401;
        $headers = array(self::$ContentTypeJSON);
        self::sendAnswer($headers, array(), $responseCode);
    }

    /**
     * Send a 400 Bad Request response.
     */
    public static function sendBadRequest()
    {
        $responseCode = 400;
        $headers = array(self::$ContentTypeJSON);
        self::sendAnswer($headers, array(), $responseCode);
    }

    /**
     * Turn an integer HTTP response code into an array of response code and response message.
     * @param $responseCode integer HTTP response code.
     * @return array|bool Array like array("Status" => array("ResponseCode => someIntCode,
     * "ResponseMessage => "someMessage")), boolean false if $responseCode is not in $ResponseCodeMessages.
     */
    public static function responseCodeToArray($responseCode)
    {
        if (in_array($responseCode, array_keys(self::$ResponseCodeMessages))) {
            return array("Status" => array("ResponseCode" => $responseCode,
                "ResponseMessage" => self::$ResponseCodeMessages[$responseCode]));
        }
        return false;
    }

    /*
     * Getters.
     */

    public static function getContentTypeJSON(): string
    {
        return self::$ContentTypeJSON;
    }

    public static function getAPIPathV1(): string
    {
        return self::$APIPathV1;
    }

}