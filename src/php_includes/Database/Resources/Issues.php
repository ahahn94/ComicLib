<?php
/**
 * Created by ahahn94
 * on 05.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Table.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Management/Connection.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Logging/Logging.php";

/**
 * Class Issues
 * Implements operations on the ComicLib/Issues table.
 */
class Issues implements Table
{

    private $connection = null; // Database connection.

    private static $columns = array("VolumeID", "APIDetailURL", "Description", "ImageFileName", "ImageURL", "IssueLocalPath",
        "IssueNumber", "Name", "ReadStatus"); // List of the valid columns.

    /**
     * Issues constructor.
     */
    public function __construct()
    {
        $this->connection = Connection::getInstance();
    }

    /**
     * Get the dataset identified by the provided ID.
     * @param $id string ID of the dataset.
     * @return array Dataset. Empty array if not found.
     */
    public function get($id)
    {
        // TODO: Implement get() method.
        return array();
    }

    /**
     * Get all datasets of the table.
     * @return array All datasets as an array of arrays. Empty array if not found.
     */
    public function getAll()
    {
        // TODO: Implement getAll() method.
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
        $columnNames = "".join(", ", $datasetColumns);
        $columnDataPlaceholders = ":".join(", :", $datasetColumns);
        // Using $columnNames and $columnDataPlaceholder assures that only valid and set array fields are inserted.
        $statement = "INSERT INTO Issues (IssueID, " . $columnNames . ") " .
            "VALUES (:IssueID, " . $columnDataPlaceholders . ")";
        $query = $this->connection->prepare($statement);

        try {
            $query->execute($dataset);
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error writing Issue {IssueID = " . $dataset["IssueID"] . "} to database!";
            Logging::logError($errorMessage);
            print($errorMessage . "<br>");
            Logging::logError($e->getMessage());
            print($e->getMessage() . "<br>");
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
        // TODO: Implement update() method.
        return 0;
    }

    /**
     * Remove the dataset identified by the provided ID.
     * @param $id string ID of the dataset to delete.
     * @return int 0 if ok, else MySQL error code.
     */
    public function remove($id)
    {
        // TODO: Implement remove() method.
        return 0;
    }
}