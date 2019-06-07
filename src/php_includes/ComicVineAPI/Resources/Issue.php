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
 * Class Issue
 * Implements access to the /issue resource of the ComicVine API.
 */
class Issue implements APIResource
{

    // List of the options to append to the resource URL.
    private static $Options = "&field_list=id,volume,api_detail_url,description,image,issue_number,name";

    /**
     * Get the resources item identified by ID from the ComicVine API.
     * @param $id string ID of the resource item to get.
     * @return array JSON array with the data of the item. Empty array if error during request.
     */
    public static function get($id)
    {
        $url = APIConfiguration::getAPIRootURL() . "issue/" . APIConfiguration::getIssuePrefix() . "$id/";
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
            $issue = array();
            $issue["IssueID"] = $decodedString["id"];
            $issue["VolumeID"] = $decodedString["volume"]["id"];
            $issue["APIDetailURL"] = $decodedString["api_detail_url"];
            $issue["Description"] = Processing::fixURLs($decodedString["description"]);
            $issue["ImageURL"] = $decodedString["image"]["small_url"];
            $issue["IssueNumber"] = $decodedString["issue_number"];
            $issue["Name"] = $decodedString["name"];
            return $issue;
        } else {
            return array();
        }
    }
}