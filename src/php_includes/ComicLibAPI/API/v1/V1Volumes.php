<?php
/**
 * Created by ahahn94
 * on 26.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicLibAPI/API/ComicLibAPIResource.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Authentication/APIAuthentication.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicLibAPI/API/APIGenerics.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicLibAPI/API/v1/V1Repo.php";

/**
 * Class V1Volumes
 * Implements functions to handle API access to the ComicLib/Volumes database table.
 */
class V1Volumes implements ComicLibAPIResource
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
                // Request to the resource root. Send all volumes.
                $volumes = $this->V1Repo->getVolumes($user["UserID"]);
                // Prepare answer.
                $headers = array(APIGenerics::getContentTypeJSON());
                $body = $volumes;
                // If volumes where found, send 200 - OK, else 404 - Not Found.
                $responseCode = (!empty($volumes) ? 200 : 404);
                APIGenerics::sendAnswer($headers, $body, $responseCode);
            } else {
                // $path has content -> request for single volume and possibly sub-resource.
                $volumeID = $this->path[0];
                $restOfPath = array_slice($this->path, 1);
                if (empty($restOfPath)) {
                    // Request for single volume. Get single volume and return it.
                    $volume = $this->V1Repo->getVolume($user["UserID"], $volumeID);
                    // Prepare answer.
                    $headers = array(APIGenerics::getContentTypeJSON());
                    $body = $volume;
                    // If volume was found, send 200 - OK, else 404 - Not Found.
                    $responseCode = (!empty($volume) ? 200 : 404);
                    APIGenerics::sendAnswer($headers, $body, $responseCode);
                } else {
                    // Request for sub-resource of the volume.
                    $subResource = $restOfPath[0];
                    $restOfPath = array_slice($restOfPath, 1);
                    if (empty($restOfPath)) {
                        // Length of the path is ok. Try to get sub-resource.
                        // Sub-resource can be issues or readstatus.
                        if ($subResource === "issues") {
                            // Send volume issues.
                            $issues = $this->V1Repo->getVolumeIssues($user["UserID"], $volumeID);
                            // Prepare answer.
                            $headers = array(APIGenerics::getContentTypeJSON());
                            $body = $issues;
                            // If volumes where found, send 200 - OK, else 404 - Not Found.
                            $responseCode = (!empty($issues) ? 200 : 404);
                            APIGenerics::sendAnswer($headers, $body, $responseCode);
                        } else if ($subResource === "readstatus") {
                            // ReadStatus requested. Send it.
                            $readStatus = $this->V1Repo->getVolumeReadStatus($user["UserID"], $volumeID);
                            // Prepare answer.
                            $headers = array(APIGenerics::getContentTypeJSON());
                            $body = $readStatus;
                            // If volumes where found, send 200 - OK, else 404 - Not Found.
                            $responseCode = (!empty($readStatus) ? 200 : 404);
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
        // Check if call to /volumes/{id}/readstatus.
        if (!empty($volumeID = $this->path[0]) && $this->path[1] === "readstatus") {
            // Check if authenticated.
            if (($user = $this->apiAuthentication->getAuthenticatedUser()) !== false) {
                // Authenticated.
                // Get request body and try to decode it from JSON.
                $requestBody = file_get_contents("php://input");
                $readStatus = json_decode($requestBody, true);
                if ($readStatus !== null) {
                    // Decoding successful. Proceed.
                    // Check if data has valid format.
                    $isRead = ctype_digit($readStatus["IsRead"]) ? intval($readStatus["IsRead"]) : false;
                    if ($isRead !== false) {
                        // Types seem ok. Proceed.
                        $isRead = ($isRead === 1) ? true : false;   // Turn int into bool.
                        // If $volumeID is valid, returns the new ReadStatus, else empty array.
                        $result = $this->V1Repo->setVolumeReadStatus($user["UserID"], $volumeID, $isRead);
                        $statusCode = (empty($result)) ? 404 : 200;
                        APIGenerics::sendAnswer(array(APIGenerics::getContentTypeJSON()), $result, $statusCode);
                    } else {
                        // Request content does not seem to be valid. Send 400 - Bad Request.
                        APIGenerics::sendBadRequest();
                    }
                } else {
                    // Conversion failed. Send 400 - Bad Request.
                    APIGenerics::sendBadRequest();
                }
            } else {
                // Not authorized.
                APIGenerics::sendUnauthorized();
            }
        } else {
            // PUT is only allowed on /volumes/{id}/readstatus.
            APIGenerics::sendMethodNotAllowed();
        }
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