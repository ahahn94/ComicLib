<?php
/**
 * Created by ahahn94
 * on 26.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/API/APISubController.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/API/APIGenerics.php";

/**
 * Class APIIssuesController
 * Implements functions to handle API access to the ComicLib/Issues database table.
 */
class APIIssuesController implements APISubController
{

    /**
     * SubController constructor.
     * @param $path array List of the parts of the path behind the subcontroller name.
     * E.g. "subcontroller/path/to/resource" becomes $subcontrollerName="subcontroller" and $path=array("path","to","resource".
     * @param $getParameters array List of the GET parameters behind the URL.
     */
    public function __construct($path, $getParameters)
    {
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
        /*
         * Todo.
         */
        // Just for testing authentication.
        print "Issues";
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