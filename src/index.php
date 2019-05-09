<?php
/**
 * Created by ahahn94
 * on 03.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Management/Connection.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Logging/Logging.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicVineAPI/Management/APICall.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicVineAPI/Resources/Publisher.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicVineAPI/Resources/Volume.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicVineAPI/Resources/VolumeIssue.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicVineAPI/Resources/Issue.php";


// Test request of publisher.
$publisher = Publisher::get("31");
print_r($publisher);
print "<br>";

// Test request of volume.
$volume = Volume::get("110496");
print_r($volume);
print "<br>";

// Test request of volumeIssues.
$volumeIssues = VolumeIssue::get("110496");
print_r($volumeIssues);
print "<br>";

// Test request of issues.
$issue = Issue::get("668770");
print_r($issue);
print "<br>";