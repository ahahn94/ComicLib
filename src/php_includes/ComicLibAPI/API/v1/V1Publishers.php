<?php
/**
 * Created by ahahn94
 * on 26.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicLibAPI/API/ComicLibAPIResource.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Authentication/APIAuthentication.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicLibAPI/API/APIGenerics.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicLibAPI/API/v1/V1Repo.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicLibAPI/API/v1/TypeConverters.php";

/**
 * Class V1Publishers
 * Implements functions to handle API access to the ComicLib/Publishers database table.
 */
class V1Publishers implements ComicLibAPIResource
{

    private $path = null;
    private $getParameters = null;
    private $apiAuthentication = null;
    private $V1Repo = null;

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
        $this->V1Repo = new V1Repo();
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
        if (($user = $this->apiAuthentication->getAuthenticatedUser()) !== false) {
            // Authenticated. Proceed.
            if (empty($this->path)) {
                // Request to the resource root. Send all publishers.
                $publishers = $this->V1Repo->getPublishers();
                // Prepare answer.
                $headers = array(APIGenerics::getContentTypeJSON());
                $body = $publishers;
                // If publishers where found, send 200 - OK, else 404 - Not Found.
                $responseCode = (!empty($publishers) ? 200 : 404);
                APIGenerics::sendAnswer($headers, $body, $responseCode);
            } else {
                // $path has content -> request for single publisher and possibly sub-resource.
                $publisherID = $this->path[0];
                $restOfPath = array_slice($this->path, 1);
                if (empty($restOfPath)) {
                    // Request for single publisher. Get single publisher and return it.
                    $publisher = $this->V1Repo->getPublisher($publisherID);
                    // Prepare answer.
                    $headers = array(APIGenerics::getContentTypeJSON());
                    $body = $publisher;
                    // If publisher was found, send 200 - OK, else 404 - Not Found.
                    $responseCode = (!empty($publisher) ? 200 : 404);
                    APIGenerics::sendAnswer($headers, $body, $responseCode);
                } else {
                    // Request for sub-resource of the publisher.
                    $subResource = $restOfPath[0];
                    $restOfPath = array_slice($restOfPath, 1);
                    if (empty($restOfPath)) {
                        // Length of the path is ok. Try to get sub-resource.
                        // Sub-resource can only be volumes.
                        if ($subResource === "volumes") {
                            // Send publisher volumes.
                            $volumes = $this->V1Repo->getPublisherVolumes($user["UserID"], $publisherID);
                            // Prepare answer.
                            $headers = array(APIGenerics::getContentTypeJSON());
                            foreach ($volumes as &$volume) {
                                // Change the data types of IsRead and CurrentPage.
                                $volume["ReadStatus"] = TypeConverters::volumeReadStatusConverter($volume["ReadStatus"]);
                                // Change the data types of IssueCount and StartYear.
                                $volume = TypeConverters::volumeConverter($volume);
                            }
                            $body = $volumes;
                            // If publishers where found, send 200 - OK, else 404 - Not Found.
                            $responseCode = (!empty($volumes) ? 200 : 404);
                            APIGenerics::sendAnswer($headers, $body, $responseCode);
                        } else {
                            // Not a valid sub-resource. Send 404 - Not Found.
                            APIGenerics::sendNotFound();
                        }
                    } else {
                        // Path is too long. Send 404 - Not Found.
                        APIGenerics::sendNotFound();
                    }
                }
            }
        } else {
            // Not authenticated.
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