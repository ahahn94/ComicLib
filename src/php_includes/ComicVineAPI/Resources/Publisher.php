<?php
/**
 * Created by ahahn94
 * on 06.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicVineAPI/Resources/APIResource.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicVineAPI/Management/APIConfiguration.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicVineAPI/Management/APICall.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicVineAPI/Processing/Processing.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Logging/Logging.php";

/**
 * Class Publisher
 * Implements access to the /publisher resource of the ComicVine API.
 */
class Publisher implements APIResource
{

    // List of the options to append to the resource URL.
    private static $Options = "&field_list=id,api_detail_url,description,image,name";

    /**
     * Get the resources item identified by ID from the ComicVine API.
     * @param $id string ID of the resource item to get.
     * @return array JSON array with the data of the item. Empty array if error during request.
     */
    public static function get($id)
    {
        $url = APIConfiguration::getAPIRootURL() . "publisher/" . APIConfiguration::getPublisherPrefix() . "$id/";
        $result = APICall::performRequest($url, self::$Options, true); // Resource is rate-limited.
        if ($result === false) {
            // No result. Error was already logged in APICall. Just return an empty array.
            return array();
        }
        return self::convertToObject($result);
    }

    /**
     * Turn a JSON string returned by the API into an object that fits to the database table.
     * @param $jsonString string JSON string returned by the ComicVine API.
     * @return array Array containing only the fields needed from the API for the database. Empty array if API error.
     */
    static function convertToObject($jsonString)
    {
        $decodedString = json_decode($jsonString, true);
        if ($decodedString["error"] == "OK") {
            // Request was successful. Continue with conversion.
            $decodedString = $decodedString["results"]; // Reduce array to only the results.
            $publisher = array();
            $publisher["PublisherID"] = $decodedString["id"];
            $publisher["APIDetailURL"] = $decodedString["api_detail_url"];
            $publisher["Description"] = Processing::fixURLs($decodedString["description"]);
            $publisher["ImageURL"] = $decodedString["image"]["original_url"];
            $publisher["Name"] = $decodedString["name"];
            return $publisher;
        } else {
            return array();
        }
    }
}