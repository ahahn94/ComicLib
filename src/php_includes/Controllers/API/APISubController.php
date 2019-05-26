<?php
/**
 * Created by ahahn94
 * on 14.05.19
 */

/**
 * Interface SubController
 * Implements the function headers for API subcontroller classes in the MVC paradigm.
 */
interface APISubController
{
    /**
     * SubController constructor.
     * @param $path array List of the parts of the path behind the subcontroller name.
     * E.g. "subcontroller/path/to/resource" becomes $subcontrollerName="subcontroller" and $path=array("path","to","resource".
     * @param $getParameters array List of the GET parameters behind the URL.
     */
    function __construct($path, $getParameters);

    /*
     * Functions for the HTTP request methods used in the API.
     */

    /**
     * Function to handle GET calls to the API.
     * Will send a json body and HTTP headers as a response.
     */
    function GET();

    /**
     * Function to handle POST calls to the API.
     * Will send a json body and HTTP headers as a response.
     */
    function POST();

    /**
     * Function to handle PUT calls to the API.
     * Will send a json body and HTTP headers as a response.
     */
    function PUT();

    /**
     * Function to handle DELETE calls to the API.
     * Will send a json body and HTTP headers as a response.
     */
    function DELETE();

    /**
     * Function to handle other calls to the API.
     * Will send a json body and HTTP headers as a response.
     */
    function other();
}