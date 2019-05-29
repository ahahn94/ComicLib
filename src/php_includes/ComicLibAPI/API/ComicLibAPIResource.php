<?php
/**
 * Created by ahahn94
 * on 14.05.19
 */

/**
 * Interface ComicLibAPIResource
 * Implements the function headers for ComicLib API resource classes in the MVC paradigm.
 */
interface ComicLibAPIResource
{
    /**
     * ComicLibAPIResource constructor.
     * @param $path array List of the parts of the path behind the api resource name.
     * E.g. "apiResource/path/to/resource" becomes $apiResourceName="apiResource" and $path=array("path","to","resource".
     * @param $getParameters array List of the GET parameters behind the URL.
     * @param $apiAuthentication APIAuthentication Object containing information on the authentication state.
     */
    function __construct($path, $getParameters, $apiAuthentication);

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