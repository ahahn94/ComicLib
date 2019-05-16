<?php
/**
 * Created by ahahn94
 * on 03.05.19
 */

// Path to the controller classes.
$ControllerPath = $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers";
// List of valid controllers. "" is the default that is used if no site is specified.
$Controllers = array("" => "VolumesController", "volumes" => "VolumesController", "volume" => "VolumeIssuesController",
    "publishers" => "PublishersController", "publisher" => "PublisherVolumesController", "download" =>
        "DownloadController");

/*
 * Dismantle URL into controller name, path and GET parameters.
 */

$url = $_GET["_url"];
$urlParts = explode("/", $url);
$controllerName = $urlParts[0];
$path = array_splice($urlParts, 1);
$getParameters = $_GET;

/*
 * Create the controller specified by $controllerName.
 * The controller will generate the requested document,
 * so this is the equivalent of loading a site via its document
 * name like "index.php" or "about.html".
 */

$controllerClassName = $Controllers[$controllerName];   // Name of the controller class.
$controllerClassPath = "$controllerClassName.php";      // Path to the controller class inside $ControllerPath.
if (empty($controllerClassName)) {
    // No valid controller name inside url. Throw 404 - Not Found error.
    require_once "$ControllerPath/NotFoundController.php";
    $notFound = new NotFoundController($path, $getParameters);
    $notFound->generateDocument();
} else {
    // Valid controller. Require controller class, create new controller object and generate document.
    require_once "$ControllerPath/$controllerClassPath";
    $controller = new $controllerClassName($path, $getParameters);
    $controller->generateDocument();
}

/**
 * Testing ImageCache.
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Caching/ImageCache.php";
$imageCache = new ImageCache();
$imageCache->updateCache();