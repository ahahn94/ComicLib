<?php
/**
 * Created by ahahn94
 * on 03.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Publishers.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Volumes.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Issues.php";

/*
 * Test reading from database tables.
 */

//// Test database for publishers.
$publisherList = array("10", "31");
$publishers = new Publishers();
foreach ($publisherList as $publisher) {
    print($publishers->get($publisher)["Name"] . "<br>");
}

// Test database for volumes.
$volumesList = array("110496", "91273", "111428", "111704");
$volumes = new Volumes();
foreach ($volumesList as $volume) {
    print($volumes->get($volume)["Name"] . "<br>");
}

// Test database for issues.
$issueList = array("535328", "668770", "672262", "674139");
$issues = new Issues();
foreach ($issueList as $issue) {
    print($issues->get($issue)["Name"] . "<br>");
}