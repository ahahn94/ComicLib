<?php
/**
 * Created by ahahn94
 * on 03.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Publishers.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Volumes.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Issues.php";

/*
 * Test removing from database tables.
 */

// Test database for issues.
$issueList = array("535328", "668770", "672262", "674139");
$issues = new Issues();
foreach ($issueList as $issue) {
    $issues->remove($issue);
}

// Test database for volumes.
$volumesList = array("110496", "91273", "111428", "111704");
$volumes = new Volumes();
foreach ($volumesList as $volume) {
    $volumes->remove($volume);
}

//// Test database for publishers.
$publisherList = array("10", "31");
$publishers = new Publishers();
foreach ($publisherList as $publisher) {
    $publishers->remove($publisher);
}