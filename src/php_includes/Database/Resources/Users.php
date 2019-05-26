<?php
/**
 * Created by ahahn94
 * on 05.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Table.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Management/Connection.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Logging/Logging.php";

/**
 * Class Users
 * Implements operations on the ComicLib/Users table.
 */
class Users implements Table
{

    private $connection = null; // Database connection.

    private static $columns = array("UserGroupID", "Name", "HashedPassword", "LastLogin",
        "APIKey"); // List of the valid columns.

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
     * @return array Dataset. Empty array if not found or error.
     */
    public function get($id)
    {
        $statement = "SELECT * FROM Users WHERE UserID = :UserID";
        $query = $this->connection->prepare($statement);
        try {
            $query->execute(array("UserID" => $id));
            if ($query->rowCount() != 0) {
                return $query->fetch(PDO::FETCH_ASSOC);
            } else return array();
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error reading User {UserID = " . $id . "} from database!";
            Logging::logError($errorMessage);
            print($errorMessage . "<br>");
            Logging::logError($e->getMessage());
            print($e->getMessage() . "<br>");
        }
        return array();
    }

    /**
     * Get the dataset identified by the provided name.
     * @param $name string Name field of the dataset.
     * @return array Dataset. Empty array if not found or error.
     */
    public function getByName($name)
    {
        $statement = "SELECT * FROM Users WHERE Name = :Name";
        $query = $this->connection->prepare($statement);
        try {
            $query->execute(array("Name" => $name));
            if ($query->rowCount() != 0) {
                return $query->fetch(PDO::FETCH_ASSOC);
            } else return array();
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error reading User {Name = " . $name . "} from database!";
            Logging::logError($errorMessage);
            print($errorMessage . "<br>");
            Logging::logError($e->getMessage());
            print($e->getMessage() . "<br>");
        }
        return array();
    }

    /**
     * Get the dataset identified by the provided apiKey.
     * @param $apiKey string APIKey field of the dataset.
     * @return array Dataset. Empty array if not found or error.
     */
    public function getByAPIKey($apiKey)
    {
        $statement = "SELECT * FROM Users WHERE APIKey = :Name";
        $query = $this->connection->prepare($statement);
        try {
            $query->execute(array("Name" => $apiKey));
            if ($query->rowCount() != 0) {
                return $query->fetch(PDO::FETCH_ASSOC);
            } else return array();
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error reading User {APIKey = " . $apiKey . "} from database!";
            Logging::logError($errorMessage);
            print($errorMessage . "<br>");
            Logging::logError($e->getMessage());
            print($e->getMessage() . "<br>");
        }
        return array();
    }

    /**
     * Get all datasets of the table.
     * @return array All datasets as an array of arrays. Empty array if not found or error.
     */
    public function getAll()
    {
        $statement = "SELECT * FROM Users";
        $query = $this->connection->prepare($statement);
        try {
            $query->execute();
            if ($query->rowCount() != 0) {
                return $query->fetchAll(PDO::FETCH_ASSOC);
            } else return array();
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error reading Users from database!";
            Logging::logError($errorMessage);
            print($errorMessage . "<br>");
            Logging::logError($e->getMessage());
            print($e->getMessage() . "<br>");
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
        $columnsPlusUserID = array("UserID");
        array_push($columnsPlusUserID, self::$columns); // Add UserID dynamically to enable auto increment.
        $datasetColumns = array_intersect($columnsPlusUserID, array_keys($dataset));
        $columnNames = "" . join(", ", $datasetColumns);
        $columnDataPlaceholders = ":" . join(", :", $datasetColumns);
        // Using $columnNames and $columnDataPlaceholder assures that only valid and set array fields are inserted.
        $statement = "INSERT INTO Users (" . $columnNames . ") " .
            "VALUES (" . $columnDataPlaceholders . ")";
        $query = $this->connection->prepare($statement);

        try {
            $query->execute($dataset);
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error writing User {UserID = " . $dataset["UserID"] . "} to database!";
            Logging::logError($errorMessage);
            print($errorMessage . "<br>");
            Logging::logError($e->getMessage());
            print($e->getMessage() . "<br>");
        }

        return $query->errorCode();
    }

    /**
     * Add or replace a dataset into the table.
     * @param $dataset array New dataset.
     * @return int 0 if ok, else MySQL error code.
     */
    public function addOrReplace($dataset)
    {
        // Collect list of columns to insert.
        $columnsPlusUserID = array("UserID");
        $columnsPlusUserID = array_merge($columnsPlusUserID, self::$columns); // Add UserID dynamically to enable auto increment.
        $datasetColumns = array_intersect($columnsPlusUserID, array_keys($dataset));
        $columnNames = "" . join(", ", $datasetColumns);
        $columnDataPlaceholders = ":" . join(", :", $datasetColumns);
        // Using $columnNames and $columnDataPlaceholder assures that only valid and set array fields are inserted.
        $statement = "REPLACE INTO Users ($columnNames) VALUES ($columnDataPlaceholders)";
        $query = $this->connection->prepare($statement);

        try {
            $query->execute($dataset);
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error writing User {UserID = " . $dataset["UserID"] . "} to database!";
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
        // Collect list of columns to update.
        $datasetColumns = array_intersect(self::$columns, array_keys($dataset));
        // Turn the array into a string like "APIDetailURL = :APIDetailURL, Description = :Description", etc.
        $dataAssignments = "" . join(", ", array_map(function ($column) {
                return "$column = :$column";
            }, $datasetColumns));

        // Using $dataAssignments assures that only valid and set array fields are updated.
        $statement = "UPDATE Users SET " . $dataAssignments . " WHERE UserID = :UserID";
        $query = $this->connection->prepare($statement);

        try {
            $query->execute($dataset);
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error updating User {UserID = " . $dataset["UserID"] . "} on the database!";
            Logging::logError($errorMessage);
            print($errorMessage . "<br>");
            Logging::logError($e->getMessage());
            print($e->getMessage() . "<br>");
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
        $statement = "DELETE FROM Users WHERE UserID = :UserID";
        $query = $this->connection->prepare($statement);
        try {
            $query->execute(array("UserID" => $id));
        } catch (Exception $e) {
            // Error handling if error while writing to database.
            $errorMessage = "Error removing User {UserID = " . $id . "} from database!";
            Logging::logError($errorMessage);
            print($errorMessage . "<br>");
            Logging::logError($e->getMessage());
            print($e->getMessage() . "<br>");
        }

        return $query->errorCode();
    }
}