<?php
/**
 * Created by ahahn94
 * on 14.05.19
 */

/**
 * Interface Controller
 * Implements the function headers for controller classes in the MVC paradigm.
 */
interface Controller
{
    /**
     * Controller constructor.
     * @param $path array List of the parts of the path behind the controller name.
     * E.g. "controller/path/to/resource" becomes $controllerName="controller" and $path=array("path","to","resource".
     * @param $getParameters array List of the GET parameters behind the URL.
     */
    function __construct($path, $getParameters);

    /**
     * Generates the document based on a view
     * and the data passed to the constructor.
     */
    function generateDocument();
}