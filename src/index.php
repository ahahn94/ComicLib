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
$publishers = new Publishers();
$publisherDatasets = $publishers->getAll();
foreach ($publisherDatasets as $dataset) {
    print($dataset["Name"] . "<br>");
}

// Test database for volumes.
$volumes = new Volumes();
$volumeDatasets = $volumes->getAll();
foreach ($volumeDatasets as $dataset) {
    print($dataset["Name"] . "<br>");
}

// Test database for issues.
$issues = new Issues();
$issueDatasets = $issues->getAll();
foreach ($issueDatasets as $dataset) {
    print($dataset["Name"] . "<br>");
}