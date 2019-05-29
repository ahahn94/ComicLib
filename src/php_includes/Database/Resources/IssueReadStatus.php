<?php
/**
 * Created by ahahn94
 * on 27.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/CustomizedView.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Management/Connection.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Logging/Logging.php";

/**
 * Class IssueReadStatus
 * Implements operations on the ComicLib/IssueReadStatus view
 */
class IssueReadStatus implements CustomizedView
{

    private $connection = null; // Database connection.

    /**
     * Issues constructor.
     */
    public function __construct()
    {
        $this->connection = Connection::getInstance();
    }

    /**
     * Get the datasets identified by the provided $userID.
     * This returns an array of the datasets, as the view groups multiple datasets under one userID.
     * @param $userID string UserID of the group of datasets.
     * @return array Datasets. Empty array if not found or error.
     */
    public function getAll($userID)
    {
        $statement = "SELECT * FROM IssueReadStatus WHERE UserID = :UserID ORDER BY Name";
        $query = $this->connection->prepare($statement);
        try {
            $query->execute(array("UserID" => $userID));
            if ($query->rowCount() != 0) {
                return $query->fetchAll(PDO::FETCH_ASSOC);
            } else return array();
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error reading IssueReadStatus {UserID = $userID} from database!";
            Logging::logError($errorMessage);
            print($errorMessage . "<br>");
            Logging::logError($e->getMessage());
            print($e->getMessage() . "<br>");
        }
        return array();
    }

    /**
     * Get the datasets identified by the provided $id and $userID.
     * @param $volumeID string Shared ID of the group of datasets.
     * @param $userID string UserID of the user to customize the datasets for.
     * @return array Datasets. Empty array if not found or error.
     */
    public function getSelection($volumeID, $userID)
    {
        $statement = "SELECT * FROM IssueReadStatus WHERE UserID = :UserID AND VolumeID = :VolumeID ORDER BY Name";
        $query = $this->connection->prepare($statement);
        try {
            $query->execute(array("UserID" => $userID, "VolumeID" => $volumeID));
            if ($query->rowCount() != 0) {
                return $query->fetchAll(PDO::FETCH_ASSOC);
            } else return array();
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error reading IssueReadStatus {UserID = $userID, VolumeID = $volumeID} from database!";
            Logging::logError($errorMessage);
            print($errorMessage . "<br>");
            Logging::logError($e->getMessage());
            print($e->getMessage() . "<br>");
        }
        return array();
    }

    /**
     * Get the dataset identified by the provided $issueID and $userID.
     * @param $issueID string IssueID of the dataset.
     * @param $userID string UserID of the user to customize the dataset for.
     * @return array Dataset. Empty array if not found or error.
     */
    public function getSingleDataset($issueID, $userID)
    {
        $statement = "SELECT * FROM IssueReadStatus WHERE UserID = :UserID AND IssueID = :IssueID";
        $query = $this->connection->prepare($statement);
        try {
            $query->execute(array("UserID" => $userID, "IssueID" => $issueID));
            if ($query->rowCount() != 0) {
                return $query->fetch(PDO::FETCH_ASSOC);
            } else return array();
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error reading IssueReadStatus {UserID = $userID, IssueID = $issueID} from database!";
            Logging::logError($errorMessage);
            print($errorMessage . "<br>");
            Logging::logError($e->getMessage());
            print($e->getMessage() . "<br>");
        }
        return array();
    }
}