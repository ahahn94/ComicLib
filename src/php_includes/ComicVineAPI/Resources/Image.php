<?php
/**
 * Created by ahahn94
 * on 06.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicVineAPI/Management/APIConfiguration.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicVineAPI/Management/APICall.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Logging/Logging.php";

/**
 * Class Image
 * Implements access to the /image resource of the ComicVine API.
 * Not using the APIResource interface as this resource does not deliver JSON objects and thus the convertToObject
 * method is not needed.
 */
class Image
{

    // List of the options to append to the resource URL.
    private static $Options = "";

    /**
     * Get the image specified by $fileName.
     * @param $fileName string Filename of the image to request.
     * @return string Base64 encoded string representation of the file or empty string on error.
     */
    public static function get($fileName)
    {
        $url = APIConfiguration::getAPIRootURL() . "image/scale_medium/" . "$fileName";
        // The image resource does not define a rate limit. Using false speeds things up significantly.
        $result = APICall::performRequest($url, self::$Options, false);
        if ($result === false){
            // No result. Error was already logged in APICall. Just return an empty array.
            return "";
        }
        return $result;
    }

}