<?php
/**
 * Created by ahahn94
 * on 24.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Configuration/Configuration.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Users.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/UserGroups.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Authentication/APIAuthentication.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Authentication/WebAuthentication.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Logging/Logging.php";

/**
 * Class DefaultDatasets
 * Implements the function to manage the default datasets on the database.
 */
class DefaultDatasets
{

    // Configuration from config.ini
    private $Config = null;

    // Database repos.
    private $UsersRepo = null;
    private $UserGroupsRepo = null;

    // Authentication classes.
    private $APIAuthentication = null;
    private $WebAuthentication = null;

    // Default datasets values.
    private $DefaultGroups = array(array("UserGroupID" => "1", "Name" => "Administrators"),
        array("UserGroupID" => "2", "Name" => "Users"));
    private $DefaultUserCredentials = null;
    private $DefaultUser = array("UserID" => "1", "UserGroupID" => "1");

    /**
     * UserAdministration constructor.
     */
    public function __construct()
    {
        $this->Config = Configuration::getConfiguration();
        $this->UsersRepo = new Users();
        $this->UserGroupsRepo = new UserGroups();
        $this->APIAuthentication = new APIAuthentication();
        $this->WebAuthentication = new WebAuthentication();
        $this->DefaultUserCredentials = $this->getDefaultUserCredentials();
        $this->DefaultUser = array_merge($this->DefaultUser, $this->DefaultUserCredentials);
    }


    /**
     * Check if the default user and groups exist. Create if not.
     * Uses the default user specified in $Config.
     * Uses the default groups specified in $DefaultGroups.
     */
    public function checkInitialization()
    {
        /*
         * Check if the default groups exist.
         */
        // Get list of user groups
        $userGroups = $this->UserGroupsRepo->getAll();
        $groupsInitialized = true;
        if (empty($userGroups)) $groupsInitialized = false;
        else {
            foreach ($this->DefaultGroups as $group) {
                // Check if group is on the database.
                if (!in_array($group, $userGroups)) $groupsInitialized = false;
            }
        }
        if (!$groupsInitialized) {
            // Default groups do not exist or are not correct. (Re-)create them.
            Logging::logInformation("The default user groups do not exist or are corrupted. (Re-)creating.");
            $this->createDefaultGroups();
        }

        /*
         * Check if the default user exists.
         */
        if (!empty($this->DefaultUser)) {
            // $DefaultUser is ok. Proceed.
            $user = $this->UsersRepo->get($this->DefaultUser["UserID"]);
            $userInitialized = true;
            if (empty($user)) $userInitialized = false;
            else {
                // Check if passwords do not match.
                if (!password_verify($this->DefaultUser["HashedPassword"], $user["HashedPassword"])) {
                    $userInitialized = false;
                }
                // Copy fields from $user that can not be predicted.
                $this->DefaultUser["HashedPassword"] = $user["HashedPassword"];
                $unpredictableFields = array("APIKey" => $user["APIKey"], "LastLogin" => $user["LastLogin"]);
                $this->DefaultUser = array_merge($this->DefaultUser, $unpredictableFields);
                // Compare arrays.
                if ($this->DefaultUser != $user) $userInitialized = false;
            }
            if (!$userInitialized) {
                // Default user does not exist or is not correct.
                Logging::logInformation("The default user does not exist or is corrupted. (Re-)creating user " .
                    $this->DefaultUser["Name"] . ".");
                $this->createDefaultUser();
            }
        } else {
            // $DefaultUser could not be populated with data. Log error.
            Logging::logError("Could not check default user!");
        }
    }

    /**
     * Get the credentials of the default user from $Config.
     * @return array Array of the user credentials.
     */
    private function getDefaultUserCredentials()
    {
        $webAdminCredentials = $this->Config["Webadmin"];
        $userName = $webAdminCredentials["AUTH_USERNAME"];
        $userPassword = $webAdminCredentials["AUTH_PASSWORD"];
        $defaultUserCredentials = array("Name" => $userName, "HashedPassword" => $userPassword);
        return $defaultUserCredentials;
    }

    /**
     * Create the default user on the Users table of the database.
     * Will override the existing default user if exists.
     */
    private function createDefaultUser()
    {
        // Add default Password, LastLogin and APIKey.
        $this->DefaultUser["LastLogin"] = "1970-01-01 00:00:00";
        $password = WebAuthentication::hashPassword($this->DefaultUserCredentials["HashedPassword"]);
        if (strpos($this->DefaultUser["Name"], ":") !== false) {
            // User name contains a colon (":"), which is not allowed (due to HTTP BASIC AUTH in the API).
            // Log error and return.
            Logging::logError
            ("The user name from the config file contains at least one ':'. This is not allowed. Not adding user.");
            return;
        }
        if ($password === false) {
            // Failed to hash password. Log error.
            Logging::logError("Could not create default user! Hashing password failed.");
            return;
        }
        $this->DefaultUser["HashedPassword"] = $password;
        $apiKey = APIAuthentication::generateAPIKey();
        if ($apiKey === false) {
            // Failed to generate APIKey. Log error.
            Logging::logError("Could not create default user! Generating APIKey failed.");
            return;
        }
        $this->DefaultUser["APIKey"] = $apiKey;
        $this->UsersRepo->addOrReplace($this->DefaultUser);
    }

    /**
     * Create the default groups on the UserGroups table of the database.
     * Will override the default groups of exist.
     */
    public function createDefaultGroups()
    {
        // Init groups.
        foreach ($this->DefaultGroups as $group) {
            $this->UserGroupsRepo->addOrReplace($group);
        }
    }
}