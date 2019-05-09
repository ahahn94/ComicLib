<?php
/**
 * Created by ahahn94
 * on 03.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Publishers.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Volumes.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Issues.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicVineAPI/Resources/Publisher.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicVineAPI/Resources/Volume.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicVineAPI/Resources/VolumeIssue.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/ComicVineAPI/Resources/Issue.php";

/*
 * Test updating database tables.
 */

//// Test database for publishers.
$publisherList = array("10", "31");
$publishers = new Publishers();
foreach ($publisherList as $publisher) {
    $dataset = Publisher::get($publisher);

    $dataset["ImageFileName"] = "foobar";

    $publishers->update($dataset);
}

//
// Test database for volumes.
$volumesList = array("110496", "91273", "111428", "111704");
$volumes = new Volumes();
foreach ($volumesList as $volume) {
    $dataset = Volume::get($volume);

    $dataset["ImageFileName"] = "foobar";

    $volumes->update($dataset);
}

// Test database for issues.
$issues = new Issues();
foreach ($volumesList as $volume) {
    // Limit issue count to 5 per volume.
    $counter = 0;
    $maxCount = 1;
    $volumeIssues = VolumeIssue::get($volume);
    foreach ($volumeIssues["Issues"] as $volumeIssue) {
        $dataset = Issue::get($volumeIssue["IssueID"]);

        $dataset["ImageFileName"] = "foobar";

        $issues->update($dataset);
        $counter++;
        if ($counter == $maxCount) break;
    }
}