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
 * Class V1Issues
 * Implements functions to handle API access to the ComicLib/Issues database table.
 */
class V1Issues implements ComicLibAPIResource
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
                // Request to the resource root. Send all issues.
                $issues = $this->V1Repo->getIssues($user["UserID"]);
                // Prepare answer.
                $headers = array(APIGenerics::getContentTypeJSON());
                $body = $issues;
                // If issues where found, send 200 - OK, else 404 - Not Found.
                $responseCode = (!empty($issues) ? 200 : 404);
                APIGenerics::sendAnswer($headers, $body, $responseCode);
            } else {
                // $path has content -> request for single issue and possibly sub-resource.
                $issueID = $this->path[0];
                $restOfPath = array_slice($this->path, 1);
                if (empty($restOfPath)) {
                    // Request for single issue. Get single issue and return it.
                    $issue = $this->V1Repo->getIssue($user["UserID"], $issueID);
                    // Prepare answer.
                    $headers = array(APIGenerics::getContentTypeJSON());
                    $body = $issue;
                    // If issue was found, send 200 - OK, else 404 - Not Found.
                    $responseCode = (!empty($issue) ? 200 : 404);
                    APIGenerics::sendAnswer($headers, $body, $responseCode);
                } else {
                    // Request for sub-resource of the issue.
                    $subResource = $restOfPath[0];
                    $restOfPath = array_slice($restOfPath, 1);
                    if (empty($restOfPath)) {
                        // Length of the path is ok. Try to get sub-resource.
                        // Sub-resource can be file or readstatus.
                        if ($subResource === "file") {
                            // Download requested. Send file.
                            $this->V1Repo->downloadIssue($user["UserID"], $issueID);
                        } else if ($subResource === "readstatus") {
                            // ReadStatus requested. Send it.
                            $readStatus = $this->V1Repo->getIssueReadStatus($user["UserID"], $issueID);
                            // Prepare answer.
                            $headers = array(APIGenerics::getContentTypeJSON());
                            $body = $readStatus;
                            // If issues where found, send 200 - OK, else 404 - Not Found.
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
        // Check if call to /issues/{id}/readstatus.
        if (!empty($issueID = $this->path[0]) && $this->path[1] === "readstatus") {
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
                    $currentPage = ctype_digit($readStatus["CurrentPage"]) ? intval($readStatus["CurrentPage"]) : false;
                    $changed =
                        (DateTime::createFromFormat("Y-m-d H:i:s", $readStatus["Changed"]) !== false
                        ) ? true : false;

                    if ($isRead !== false && $currentPage !== false && $changed) {
                        // Types seem ok. Proceed.
                        $isRead = ($isRead === 1) ? true : false;   // Turn int into bool.
                        $changed = $readStatus["Changed"];
                        $dataset = array("IsRead" => $isRead, "CurrentPage" => $currentPage, "Changed" => $changed);
                        // If $issueID is valid, returns the new ReadStatus, else empty array.
                        $result = $this->V1Repo->setIssueReadStatus($user["UserID"], $issueID, $dataset);
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
            // PUT is only allowed on /issues/{id}/readstatus.
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