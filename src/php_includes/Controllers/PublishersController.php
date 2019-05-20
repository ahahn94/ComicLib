<?php
/**
 * Created by ahahn94
 * on 14.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/Controller.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Publishers.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Caching/ImageCache.php";

/**
 * Class PublishersController
 * Implements the controller for the publishers overview.
 */
class PublishersController implements Controller
{

    private static $CurrentPage = "publishers";     // Current page. Specifies the menu entry to highlight.
    private static $CachePath = "";                 // Path to the image cache.
    private $publishers = array();                  // Publishers to show in the view.

    /**
     * Controller constructor.
     * @param $path array List of the parts of the path behind the controller name.
     * E.g. "controller/path/to/resource" becomes $controllerName="controller" and $path=array("path","to","resource".
     * @param $getParameters array List of the GET parameters behind the URL.
     */
    public function __construct($path, $getParameters)
    {
        // Prepare data for view.
        $publishersRepo = new Publishers();
        $this->publishers = $publishersRepo->getAll();
        self::$CachePath = ImageCache::getImageCachePath();
    }

    /**
     * Generates the document based on a view
     * and the data passed to the constructor.
     */
    function generateDocument()
    {
        // Check if there are publishers to show in the view.
        if (!empty($this->publishers)) {
            // There are publishers to show. Send view PublishersView.
            include $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Views/PublishersView.php";
        } else {
            // $this->volumes is empty. Show "Empty Database" view.
            include $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Views/EmptyDatabaseView.php";
        }
    }
}