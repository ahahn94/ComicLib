<?php
/**
 * Created by ahahn94
 * on 03.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Storage/StorageManager.php";

/*
 * Test scanning storage and adding comics to database.
 */
$storageManager = new StorageManager();
$storageManager->scanStorage();