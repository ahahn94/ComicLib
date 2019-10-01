<?php
/**
 * Created by ahahn94
 * on 26.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicLibAPI/API/ComicLibAPIResource.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicLibAPI/API/APIGenerics.php";

/**
 * Class V1Online
 * Implements functions to handle the online status of the API.
 */
class V1Online implements ComicLibAPIResource
{

    private $path = null;
    private $getParameters = null;
    private $apiAuthentication = null;

    /**
     * ComicLibAPIResource constructor.
     * @param $path array List of the parts of the path behind the api resource name.
     * E.g. "apiResource/path/to/resource" becomes $apiResourceName="apiResource" and $path=array("path","to","resource".
     * @param $getParameters array List of the GET parameters behind the URL.
     * @param $apiAuthentication APIAuthentication Object containing information on the authentication state.
     */
    public function __construct($path, $getParameters, $apiAuthentication)
    {
        $this->path = $path;
        $this->getParameters = $getParameters;
        $this->apiAuthentication = $apiAuthentication;
        $requestMethod = APIGenerics::getRequestMethod();
        // Call the function matching the HTTP request method.
        if ($requestMethod === "GET") {
            $this->GET();
        } else if ($requestMethod === "POST") {
            $this->POST();
        } else if ($requestMethod === "PUT") {
            $this->PUT();
        } else if ($requestMethod === "DELETE") {
            $this->DELETE();
        } else {
            $this->other();
        }
    }

    /**
     * Function to handle GET calls to the API.
     * Will send a json body and HTTP headers as a response.
     */
    function GET()
    {
        // If no sub-path, send OK.
        if (empty($this->path)) {
            // Send 200 OK with empty content.
            $responseCode = 200;
            $headers = array(APIGenerics::getContentTypeJSON());
            $body = array();
            APIGenerics::sendAnswer($headers, $body, $responseCode);
        } else {
            // Else send Not Found.
            APIGenerics::sendNotFound();
        }
    }

    /**
     * Function to handle POST calls to the API.
     * Will send a json body and HTTP headers as a response.
     */
    function POST()
    {
        APIGenerics::sendMethodNotAllowed();
    }

    /**
     * Function to handle PUT calls to the API.
     * Will send a json body and HTTP headers as a response.
     */
    function PUT()
    {
        APIGenerics::sendMethodNotAllowed();
    }

    /**
     * Function to handle DELETE calls to the API.
     * Will send a json body and HTTP headers as a response.
     */
    function DELETE()
    {
        APIGenerics::sendMethodNotAllowed();
    }

    /**
     * Function to handle other calls to the API.
     * Will send a json body and HTTP headers as a response.
     */
    function other()
    {
        APIGenerics::sendMethodNotAllowed();
    }
}