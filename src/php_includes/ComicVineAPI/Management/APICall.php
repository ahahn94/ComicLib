<?php
/**
 * Created by ahahn94
 * on 06.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Logging/Logging.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicVineAPI/Management/APIConfiguration.php";

/**
 * Class APICall
 * Class that handles calls to the API.
 */
class APICall
{

    /**
     * Perform an API request.
     * @param $url string URL of the resource item to request.
     * @param $options string String containing the options to append to the end of the request URL.
     * @param $rateLimit boolean Does the resource to request have a rate limit? Adds 2 second to execution time.
     * @return bool|string Result as string. False if error during request.
     */
    public static function performRequest($url, $options, $rateLimit)
    {
        $assembledURL = APIConfiguration::assembleURL($url, $options);  // Add auth etc. to the URL.
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => $assembledURL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "GET",
            CURLOPT_HTTPHEADER => array(
                "cache-control: no-cache"
            ),
            CURLOPT_USERAGENT => "ComicLib"
        ));

        $result = curl_exec($curl);

        if ($result === false) {
            // Error executing curl request. Log and print error.
            $errorMessage = "Error requesting " . $url . "!";
            Logging::logError($errorMessage);
            print($errorMessage . "<br>");
            Logging::logError(curl_error($curl));
            print(curl_error($curl) . "<br>");
        }

        curl_close($curl);

        if ($rateLimit){
            // Slow things down to avoid hitting the API rate limit of one request per second.
            sleep(2);   // 2 seconds to keep it safe.
        }

        return $result;
    }

}