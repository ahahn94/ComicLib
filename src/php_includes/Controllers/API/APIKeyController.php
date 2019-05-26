<?php
/**
 * Created by ahahn94
 * on 26.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/API/APISubController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/API/APIGenerics.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Authentication/APIAuthentication.php";

/**
 * Class APIKeyController
 * Implements functions to handle authentication and access to the APIKey column of the ComicLib/Users table.
 */
class APIKeyController implements APISubController
{

    private $APIAuthentication = null;

    /**
     * SubController constructor.
     * @param $path array List of the parts of the path behind the subcontroller name.
     * E.g. "subcontroller/path/to/resource" becomes $subcontrollerName="subcontroller" and $path=array("path","to","resource".
     * @param $getParameters array List of the GET parameters behind the URL.
     */
    public function __construct($path, $getParameters)
    {
        $this->APIAuthentication = new APIAuthentication();
        // Call the function matching the HTTP request method.
        $requestMethod = APIGenerics::getRequestMethod();
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
        // Authenticate with username and password from HTTP Authorization header.
        $apiKey = $this->APIAuthentication->basicAuthentication();
        if ($apiKey !== false) {
            // Successfully authenticated. Send APIKey.
            $responseCode = 200;
            $headers = array(APIGenerics::getContentTypeJSON());
            $body = array("APIKey" => $apiKey);
            APIGenerics::sendAnswer($headers, $body, $responseCode);
        } else {
            // Authentication failed. Send 401 Unauthorized.
            APIGenerics::sendUnauthorized();
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