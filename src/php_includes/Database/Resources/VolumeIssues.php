<?php
/**
 * Created by ahahn94
 * on 09.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/View.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Management/Connection.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Logging/Logging.php";

/**
 * Class PublisherVolumes
 * Implements operations on the ComicLib/VolumeIssues view.
 */
class VolumeIssues implements View
{

    private $connection = null;  // Database connection.

    /**
     * VolumeIssues constructor.
     */
    public function __construct()
    {
        $this->connection = Connection::getInstance();
    }

    /**
     * Get the datasets identified by the provided ID.
     * This returns an array of the datasets, as the view groups multiple datasets under one ID.
     * @param $id string Shared ID of the group of datasets.
     * @return array Datasets. Empty array if not found or error.
     */
    public function getSelection($id)
    {
        $statement = "SELECT * FROM VolumeIssues WHERE VolumeID = :VolumeID ORDER BY IssueNumber";
        $query = $this->connection->prepare($statement);
        try {
            $query->execute(array("VolumeID" => $id));
            if ($query->rowCount() != 0) {
                return $query->fetchAll(PDO::FETCH_ASSOC);
            } else return array();
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error reading VolumeIssues {VolumeID = " . $id . "} from database!";
            Logging::logError($errorMessage);
            print($errorMessage . "<br>");
            Logging::logError($e->getMessage());
            print($e->getMessage() . "<br>");
        }
        return array();
    }

    /**
     * Get all datasets of the view.
     * @return array All datasets as an array of arrays. Empty array if not found or error.
     */
    public function getAll()
    {
        $statement = "SELECT * FROM VolumeIssues";
        $query = $this->connection->prepare($statement);
        try {
            $query->execute();
            if ($query->rowCount() != 0) {
                return $query->fetchAll(PDO::FETCH_ASSOC);
            } else return array();
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error reading VolumeIssues from database!";
            Logging::logError($errorMessage);
            print($errorMessage . "<br>");
            Logging::logError($e->getMessage());
            print($e->getMessage() . "<br>");
        }
        return array();
    }
}