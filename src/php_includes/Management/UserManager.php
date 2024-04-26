<?php
/**
 * Created by ahahn94
 * on 21.07.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Users.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/UserGroups.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Authentication/APIAuthentication.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Authentication/WebAuthentication.php";

/**
 * Class UserManager
 * Handles changes to the users.
 */
class UserManager
{
    private static $groupIDs = ["admin" => 1, "user" => 2]; // List for looking up the UserGroupIDs of admin and user.
    private static $defaultUserID = 1;      // The UserID of the default user. The default user has additional privileges.

    // Database repositories.
    private $usersRepo;
    private $groupsRepo;

    /**
     * UserManager constructor.
     */
    public function __construct()
    {
        // Initialize database repos.
        $this->usersRepo = new Users();
        $this->groupsRepo = new UserGroups();
    }

    /**
     * Change the password of the logged in user.
     * The password of the default user can not be changed via web-interface,
     * change it in the config.ini file instead.
     * @param $current string The current password.
     * @param $new string The new password.
     * @param $newRepeat string Repetition of the new password.
     * @return string Empty if successful, else error message to display.
     */
    public function changePassword($current, $new, $newRepeat): string
    {
        // Can not change password of default user via web-interface. Modify config file instead.
        if (self::getCurrentUser()["UserID"] == self::$defaultUserID) {
            return "Changing the password of the default user via web-interface is prohibited. Change the config.ini file instead.";
        } else {
            // Check if passwords match.
            if (strcmp($new, $newRepeat) === 0) {
                // Passwords match. Proceed.
                // Check if current password is correct.
                $userID = self::getCurrentUser()["UserID"];
                $user = $this->usersRepo->get($userID);
                if (WebAuthentication::verifyPassword($current, $user["HashedPassword"])) {
                    // Current password is correct. Update password.
                    $passwordHash = WebAuthentication::hashPassword($new);
                    if ($passwordHash === false) {
                        // Hashing password failed. Show error.
                        return "Hashing password failed. Password was not changed.";
                    }
                    $user["HashedPassword"] = $passwordHash;
                    $result = $this->usersRepo->update($user);
                    if ($result == 0) {
                        // Successfully changed password. Return empty string.
                        return "";
                    } else {
                        // Error updating dataset on database. Show error.
                        return "Error updating the password on the database. Password was not changed.";
                    }
                } else {
                    // Current password is wrong. Show error.
                    return "Wrong current password.";
                }
            } else {
                // Passwords do not match. Show error.
                return "Passwords do not match.";
            }
        }
    }

    /**
     * Add a user to the Users table of the database.
     * @param $name string Name of the new user.
     * @param $password string Password of the new user.
     * @param $passwordRepeat string Repetition of the password.
     * @param $userGroupName string Name of the user group of the new user.
     * @return string Empty if successful, else error message to display.
     */
    public function addUser($name, $password, $passwordRepeat, $userGroupName): string
    {
        // Check if administrator.
        if (self::getCurrentUser()["UserGroupID"] == self::getGroupIDs()["admin"]) {
            // Try to add the new user.

            // Check if the username contains a ":", which is prohibited due to incompatibility with HTTP Basic Auth.
            if (strpos($name, ":") !== false) {
                // Username contains a ":". Show error.
                return "The name contains a \":\", which is not allowed.";
            }

            //Check if name exists on the database.
            $users = $this->usersRepo->getAll();
            $names = array_map(function ($item) {
                return $item["Name"];
            }, $users);
            if (!in_array($name, $names)) {
                // Username does not already exist. Proceed.
                // Check if passwords match.
                if (strcmp($password, $passwordRepeat) === 0) {
                    // Passwords match. Create new user.

                    // Prepare UserGroupID and check if trying to create admin user.
                    $userGroups = $this->groupsRepo->getAll();
                    $group = array_shift(array_filter($userGroups, function ($item) use ($userGroupName) {
                        return strcmp($item["Name"], $userGroupName) == 0;
                    }));

                    if (!isset($group["UserGroupID"])) {
                        // GroupID could not be found. Show error.
                        return "Invalid group name. User not created.";
                    }

                    if ($group["UserGroupID"] == self::getGroupIDs()["admin"]) {
                        if (self::getCurrentUser()["UserID"] != self::getDefaultUserID()) {
                            // Trying to create an admin user using a normal admin account. Show error.
                            return "Only the default user can create new admin accounts.";
                        }
                    }

                    $userGroupID = $group["UserGroupID"];
                    $passwordHash = WebAuthentication::hashPassword($password);
                    if ($passwordHash === false) {
                        // Error hashing password. Show error.
                        return "Hashing password failed. User not created.";
                    }
                    $lastLogin = "1970-01-01 00:00:00";
                    $apiKey = APIAuthentication::generateAPIKey();
                    $newUser = array("Name" => $name, "HashedPassword" => $passwordHash, "UserGroupID" => $userGroupID,
                        "LastLogin" => $lastLogin, "APIKey" => $apiKey);
                    $result = $this->usersRepo->add($newUser);
                    if ($result == 0) {
                        // User successfully created. Return empty string.
                        return "";
                    } else {
                        // Error adding user to database. Show error.
                        return "Error adding user to database.";
                    }
                } else {
                    // Passwords do not match. Show error.
                    return "Passwords do not match.";
                }
            } else {
                // Username already exists. Show error.
                return "A user with this name already exists.";
            }
        } else {
            // Insufficient privileges.
            return "You have to be a member of the administrators group to add new users.";
        }
    }

    /**
     * Change the user group of a user.
     * Only the default user is allowed to change a users group.
     * @param $userID string UserID of the user to change the group of.
     * @param $userGroupName string Name of the new group.
     * @return string Empty if successful, else error message.
     */
    public function changeUserGroup($userID, $userGroupName)
    {
        // Check if default user. Prohibited to other users.
        if (UserManager::getCurrentUser()["UserID"] == UserManager::getDefaultUserID()) {
            // Default user. Proceed.
            // Check if trying to change default users group.
            if ($userID == UserManager::getDefaultUserID()) {
                // Trying to change the group of the default user. Show error.
                return "The group of the default user can not be changed.";
            }
            // Prepare UserGroupID.
            $userGroups = $this->groupsRepo->getAll();
            $group = array_shift(array_filter($userGroups, function ($item) use ($userGroupName) {
                return strcmp($item["Name"], $userGroupName) == 0;
            }));

            if (!isset($group["UserGroupID"])) {
                // GroupID could not be found. Show error.
                return "Invalid group name. Group not changed.";
            }

            // Update group of the user.
            $userGroupID = $group["UserGroupID"];
            $user = $this->usersRepo->get($userID);
            $user["UserGroupID"] = $userGroupID;
            $result = $this->usersRepo->update($user);
            if ($result == 0) {
                // Successfully changed group. Return empty string.
                return "";
            } else {
                // Error while updating database. Show error.
                return "Error updating user on the database. Group not changed.";
            }

        } else {
            // Not the default user. Show error.
            return "Only the default user is allowed to change user groups.";
        }
    }

    /**
     * Remove a user from the database.
     * Requires that the logged in user is a member of the administrators group.
     * The default user can not be deleted.
     * @param $userID string UserID of the user to remove from the database.
     * @return string Empty if successful, else error message to display.
     */
    public function deleteUser($userID): string
    {
        // Check if administrator.
        if (self::getCurrentUser()["UserGroupID"] == self::getGroupIDs()["admin"]) {
            // User is admin. Proceed.
            // Check if trying to delete default user.
            if ($userID != self::$defaultUserID) {
                // Not trying to delete default user. Proceed.

                // Check if trying to delete admin.
                $user = $this->usersRepo->get($userID);
                if ($user["UserGroupID"] == UserManager::getGroupIDs()["admin"]) {
                    // Trying to delete admin. Only allowed to default user.
                    if (UserManager::getCurrentUser()["UserID"] != UserManager::getDefaultUserID()) {
                        // Not the default user. Show error.
                        return "Only the default user can delete accounts of the groups \"Administrators\"";
                    }
                }
                //Delete user.
                $result = $this->usersRepo->remove($userID);
                if ($result == 0) {
                    // User successfully deleted. Return empty string.
                    return "";
                } else {
                    // Error removing user from the database. Show error.
                    return "Error removing user from the database.";
                }
            } else {
                // Trying to delete the default user. Show error.
                return "The default user can not be deleted.";
            }
        } else {
            // Insufficient privileges.
            return "You have to be a member of the administrators group to add new users.";
        }
    }

    /**
     * Regenerate the API key of a user.
     * Requires authentication via password.
     * @param $password string The password of the user trying to regenerate the API key.
     * @return string Empty if successful, else error message.
     */
    public function regenerateAPIKey($password)
    {
        // Check if password is correct.
        $userID = self::getCurrentUser()["UserID"];
        $user = $this->usersRepo->get($userID);
        if (WebAuthentication::verifyPassword($password, $user["HashedPassword"])) {
            // Password is correct. Generate new API key.
            $apiKey = APIAuthentication::generateAPIKey();
            if ($apiKey === false) {
                // Hashing password failed. Show error.
                return "Generating API key failed. API key was not changed.";
            }
            $user["APIKey"] = $apiKey;
            $result = $this->usersRepo->update($user);
            if ($result == 0) {
                // Successfully changed API key. Return empty string.
                return "";
            } else {
                // Error updating dataset on database. Show error.
                return "Error updating the API key on the database. API key was not changed.";
            }
        } else {
            // Password is wrong. Show error.
            return "Wrong password.";
        }
    }

    /*
     * Getters.
     */

    public static function getGroupIDs(): array
    {
        return self::$groupIDs;
    }

    public static function getCurrentUser(): array
    {
        return $_SESSION["User"];
    }

    public static function getDefaultUserID(): int
    {
        return self::$defaultUserID;
    }

}