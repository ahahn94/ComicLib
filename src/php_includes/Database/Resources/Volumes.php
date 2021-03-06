<?php
/**
 * Created by ahahn94
 * on 05.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Table.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Management/Connection.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Logging/Logging.php";

/**
 * Class Volumes
 * Implements operations on the ComicLib/Volumes table.
 */
class Volumes implements Table
{

    private $connection = null; // Database connection.

    private static $columns = array("PublisherID", "APIDetailURL", "Description", "ImageFileName", "ImageURL", "Name",
        "StartYear", "VolumeLocalPath"); // List of the valid columns.

    /**
     * Volumes constructor.
     */
    public function __construct()
    {
        $this->connection = Connection::getInstance();
    }

    /**
     * Get the dataset identified by the provided ID.
     * @param $id string ID of the dataset.
     * @return array Dataset. Empty array if not found or error.
     */
    public function get($id)
    {
        $statement = "SELECT * FROM Volumes WHERE VolumeID = :VolumeID";
        $query = $this->connection->prepare($statement);
        try {
            $query->execute(array("VolumeID" => $id));
            if ($query->rowCount() != 0) {
                return $query->fetch(PDO::FETCH_ASSOC);
            } else return array();
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error reading Volume {VolumeID = " . $id . "} from database!";
            Logging::logError($errorMessage);
            Logging::logError($e->getMessage());
        }
        return array();
    }

    /**
     * Get all datasets of the table.
     * @return array All datasets as an array of arrays. Empty array if not found or error.
     */
    public function getAll()
    {
        $statement = "SELECT * FROM Volumes ORDER BY Name";
        $query = $this->connection->prepare($statement);
        try {
            $query->execute();
            if ($query->rowCount() != 0) {
                return $query->fetchAll(PDO::FETCH_ASSOC);
            } else return array();
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error reading Volumes from database!";
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
        $statement = "INSERT INTO Volumes (VolumeID, " . $columnNames . ") " .
            "VALUES (:VolumeID, " . $columnDataPlaceholders . ")";
        $query = $this->connection->prepare($statement);

        try {
            $query->execute($dataset);
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error writing Volume {VolumeID = " . $dataset["VolumeID"] . "} to database!";
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
        $statement = "UPDATE Volumes SET " . $dataAssignments . " WHERE VolumeID = :VolumeID";
        $query = $this->connection->prepare($statement);

        try {
            $query->execute($dataset);
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error updating Volume {VolumeID = " . $dataset["VolumeID"] . "} on the database!";
            Logging::logError($errorMessage);
            Logging::logError($e->getMessage());
        }

        return $query->errorCode();
    }

    /**
     * Remove the dataset identified by the provided ID.
     * @param $id string ID of the dataset to delete.
     * @return int 0 if ok, else MySQL error code.
     */
    public function remove($id)
    {
        $statement = "DELETE FROM Volumes WHERE VolumeID = :VolumeID";
        $query = $this->connection->prepare($statement);
        try {
            $query->execute(array("VolumeID" => $id));
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error removing Volume {VolumeID = " . $id . "} from database!";
            Logging::logError($errorMessage);
            Logging::logError($e->getMessage());
        }
        return $query->errorCode();
    }
}