<?php
/**
 * Created by ahahn94
 * on 03.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/PublisherVolumes.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/VolumeIssues.php";

/*
 * Test reading from database views.
 */

// Test PublisherVolumes view.
print("PublisherVolumes getSelection<br>");
$publisherVolumes = new PublisherVolumes();
$volumes = $publisherVolumes->getSelection("10");
foreach ($volumes as $volume) {
    print($volume["Name"] . "<br>");
}
print("<br>");
print("PublisherVolumes getAll<br>");
$volumes = $publisherVolumes->getAll();
foreach ($volumes as $volume) {
    print($volume["Name"] . "<br>");
}
print("<br>");

// Test VolumeIssues view.
print("VolumeIssues getSelection<br>");
$volumeIssues = new VolumeIssues();
$issues = $volumeIssues->getSelection("110496");
foreach ($issues as $issue) {
    print($issue["IssueID"] . "<br>");
}
print("<br>");
print("VolumeIssues getAll<br>");
$issues = $volumeIssues->getAll();
foreach ($issues as $issue) {
    print($issue["IssueID"] . "<br>");
}
print("<br>");