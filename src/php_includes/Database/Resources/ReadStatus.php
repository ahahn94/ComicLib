<?php
/**
 * Created by ahahn94
 * on 05.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Table.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/VolumeIssues.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Management/Connection.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Logging/Logging.php";

/**
 * Class ReadStatus
 * Implements operations on the ComicLib/ReadStatus table.
 * Does not implement Table interface, as it needs multiple IDs for the SELECTs.
 */
class ReadStatus
{

    private $connection = null; // Database connection.

    private static $columns = array("IsRead", "CurrentPage", "Changed"); // List of the valid columns.

    /**
     * Issues constructor.
     */
    public function __construct()
    {
        $this->connection = Connection::getInstance();
    }

    /**
     * Update the ReadStatus of a single issue for a single user.
     * @param $issueID string IssueID of the issue to update the ReadStatus for.
     * @param $userID string UserID of the user to change the ReadStatus for.
     * @param $dataset array New ReadStatus (boolean), CurrentPage (integer) and Changed (datetime).
     */
    public function updateIssue($issueID, $userID, $dataset)
    {
        $isRead = ($dataset["IsRead"] === true) ? 1 : 0;   // Turn boolean into TINYINT.
        // Fill update dataset with data to update.
        $updateDataset = array("IssueID" => $issueID, "UserID" => $userID, "IsRead" => $isRead,
            "CurrentPage" => $dataset["CurrentPage"], "Changed" => $dataset["Changed"]);
        $this->update($updateDataset);
    }

    /**
     * Update the ReadStatus of a whole volume for a single user.
     * @param $volumeID string VolumeID of the issues to update the ReadStatus for.
     * @param $userID string UserID of the user to change the ReadStatus for.
     * @param $readStatus boolean New ReadStatus as a boolean.
     * @param $changed string Datetime of the change.
     */
    public function updateVolume($volumeID, $userID, $readStatus, $changed)
    {
        $isRead = ($readStatus === true) ? 1 : 0;   // Turn boolean into TINYINT.
        // Fill dataset with data to update. Reset CurrentPage to page 0.
        $dataset = array("UserID" => $userID, "IsRead" => $isRead, "CurrentPage" => 0, "Changed" => $changed);
        // Get volume issues.
        $volumeIssuesRepo = new VolumeIssues();
        $volumeIssues = $volumeIssuesRepo->getSelection($volumeID);
        if (!empty($volumeIssues)) {
            foreach ($volumeIssues as $volumeIssue) {
                $issue = array_merge($dataset, array("IssueID" => $volumeIssue["IssueID"]));
                $this->update($issue);
            }
        } else {
            // Error reading volume issues. Log error.
            $errorMessage = "Error updating ReadStatus {UserID =  $userID, VolumeID = $volumeID} on the database!";
            Logging::logError($errorMessage);
        }
    }

    /**
     * Get the dataset identified by the provided IDs.
     * @param $userID string ID of the dataset.
     * @param $issueID string ID of the dataset.
     * @return array Dataset. Empty array if not found or error.
     */
    public function get($userID, $issueID)
    {
        $statement = "SELECT * FROM ReadStatus WHERE UserID = :UserID AND IssueID = :IssueID";
        $query = $this->connection->prepare($statement);
        try {
            $query->execute(array("UserID" => $userID, "IssueID" => $issueID));
            if ($query->rowCount() != 0) {
                return $query->fetch(PDO::FETCH_ASSOC);
            } else return array();
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error reading ReadStatus {UserID =  $userID, IssueID = $issueID} from database!";
            Logging::logError($errorMessage);
            Logging::logError($e->getMessage());
        }
        return array();
    }

    /**
     * Get all datasets of the table filtered by IssueID.
     * @param $id string ID of the dataset.
     * @return array All datasets as an array of arrays. Empty array if not found or error.
     */
    public function getSelectionByIssue($id)
    {
        $statement = "SELECT * FROM ReadStatus WHERE IssueID = :IssueID";
        $query = $this->connection->prepare($statement);
        try {
            $query->execute(array("IssueID" => $id));
            if ($query->rowCount() != 0) {
                return $query->fetchAll(PDO::FETCH_ASSOC);
            } else return array();
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error reading ReadStatus {IssueID = $id} from database!";
            Logging::logError($errorMessage);
            Logging::logError($e->getMessage());
        }
        return array();
    }

    /**
     * Get all datasets of the table filtered by UserID.
     * @param $id string ID of the dataset.
     * @return array All datasets as an array of arrays. Empty array if not found or error.
     */
    public function getSelectionByUser($id)
    {
        $statement = "SELECT * FROM ReadStatus WHERE UserID = :UserID";
        $query = $this->connection->prepare($statement);
        try {
            $query->execute(array("UserID" => $id));
            if ($query->rowCount() != 0) {
                return $query->fetchAll(PDO::FETCH_ASSOC);
            } else return array();
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error reading ReadStatus {UserID =  $id} from database!";
            Logging::logError($errorMessage);
            Logging::logError($e->getMessage());
        }
        return array();
    }

    /**
     * Add a new dataset to the table.
     * @param $dataset array New dataset.
     * @return int 0 if ok, else MySQL error code.
     */
    public function add($dataset)
    {
        // Collect list of columns to insert.
        $datasetColumns = array_intersect(self::$columns, array_keys($dataset));
        $columnNames = "" . join(", ", $datasetColumns);
        $columnDataPlaceholders = ":" . join(", :", $datasetColumns);
        // Using $columnNames and $columnDataPlaceholder assures that only valid and set array fields are inserted.
        $statement = "INSERT INTO ReadStatus (UserID, IssueID, " . $columnNames . ") " .
            "VALUES (:UserID, :IssueID, " . $columnDataPlaceholders . ")";
        $query = $this->connection->prepare($statement);

        try {
            $query->execute($dataset);
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error writing ReadStatus {UserID =  " . $dataset["UserID"] . ", IssueID = " .
                $dataset["IssueID"] . "} to database!";
            Logging::logError($errorMessage);
            Logging::logError($e->getMessage());
        }

        return $query->errorCode();
    }

    /**
     * Update a dataset.
     * @param $dataset array Dataset with the updated data and the ID of the dataset to change.
     * @return int 0 if ok, else MySQL error code.
     */
    public function update($dataset)
    {
        // Collect list of columns to update.
        $datasetColumns = array_intersect(self::$columns, array_keys($dataset));
        // Turn the array into a string like "APIDetailURL = :APIDetailURL, Description = :Description", etc.
        $dataAssignments = "" . join(", ", array_map(function ($column) {
                return "$column = :$column";
            }, $datasetColumns));

        // Using $dataAssignments assures that only valid and set array fields are updated.
        $statement = "UPDATE ReadStatus SET " . $dataAssignments . " WHERE UserID = :UserID AND IssueID = :IssueID";
        $query = $this->connection->prepare($statement);

        try {
            $query->execute($dataset);
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error updating ReadStatus {UserID =  " . $dataset["UserID"] . ", IssueID = " .
                $dataset["IssueID"] . "} on the database!";
            Logging::logError($errorMessage);
            Logging::logError($e->getMessage());
        }

        return $query->errorCode();
    }

    /**
     * Remove the dataset identified by the provided IDs.
     * @param $userID string ID of the dataset.
     * @param $issueID string ID of the dataset.
     * @return int 0 if ok, else MySQL error code.
     */
    public function remove($userID, $issueID)
    {
        $statement = "DELETE FROM ReadStatus WHERE UserID = :UserID AND IssueID = :IssueID";
        $query = $this->connection->prepare($statement);
        try {
            $query->execute(array("UserID" => $userID, "IssueID" => $issueID));
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error removing ReadStatus {UserID =  $userID, IssueID = $issueID} from database!";
            Logging::logError($errorMessage);
            Logging::logError($e->getMessage());
        }

        return $query->errorCode();
    }
}