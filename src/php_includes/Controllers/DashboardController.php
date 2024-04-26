<?php
/**
 * Created by ahahn94
 * on 21.07.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Controllers/Controller.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Management/UserManager.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Users.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/UserGroups.php";

/**
 * Class DashboardController
 * Implements the controller for the management dashboard view.
 */
class DashboardController implements Controller
{

    private $users;
    private $user;
    private $groups;
    private $errorMessage = "";

    private static $CurrentPage = "dashboard";       // Current page. Specifies the menu entry to highlight.

    /**
     * Controller constructor.
     * @param $path array List of the parts of the path behind the controller name.
     * E.g. "controller/path/to/resource" becomes $controllerName="controller" and $path=array("path","to","resource".
     * @param $getParameters array List of the GET parameters behind the URL.
     */
    public function __construct($path, $getParameters)
    {
        // Handle POST requests.
        if (isset($_POST)) {
            $userManager = new UserManager();
            $action = $_POST["Action"];
            switch ($action) {
                case "ChangePassword":
                    // Handle data from the Change Password form.
                    $currentPassword = $_POST["CurrentPassword"];
                    $newPassword = $_POST["NewPassword"];
                    $newPasswordRepeat = $_POST["NewPasswordRepeat"];
                    $this->errorMessage = $userManager->changePassword($currentPassword, $newPassword, $newPasswordRepeat);
                    break;
                case "AddUser":
                    // Handle data from the Add User form.
                    $name = $_POST["Name"];
                    $password = $_POST["Password"];
                    $passwordRepeat = $_POST["PasswordRepeat"];
                    $userGroupName = $_POST["UserGroupName"];
                    $this->errorMessage = $userManager->addUser($name, $password, $passwordRepeat, $userGroupName);
                    break;
                case "ChangeUserGroup":
                    // Handle data from the Change User Group form.
                    $userID = $_POST["UserID"];
                    $userGroupName = $_POST["UserGroupName"];
                    $this->errorMessage = $userManager->changeUserGroup($userID, $userGroupName);
                    break;
                case "DeleteUser":
                    // Handle data from the Delete User form.
                    $userID = $_POST["UserID"];
                    $this->errorMessage = $userManager->deleteUser($userID);
                    break;
                case "RegenerateAPIKey":
                    // Handle data from the Regenerate API Key form.
                    $password = $_POST["Password"];
                    $this->errorMessage = $userManager->regenerateAPIKey($password);
                    break;
            }
        }

        $this->user = UserManager::getCurrentUser();    // Content of the view is dynamic for different users.

        // If user is admin, show list of users and group selection.
        if ($this->user["UserGroupID"] == UserManager::getGroupIDs()["admin"]) {
            // Get users to display in the view.
            $usersRepo = new Users();
            $userGroupsRepo = new UserGroups();
            $users = $usersRepo->getAll();
            // Add GroupName to each user.
            $userGroups = array();
            $allGroups = $userGroupsRepo->getAll();
            array_walk($allGroups, function ($item) use (&$userGroups) {
                $userGroups = array_merge($userGroups, array($item["Name"] => $item["UserGroupID"]));
            });
            $userGroups = array_flip($userGroups);
            $this->users = array_map(function ($item) use ($userGroups) {
                $item["GroupName"] = $userGroups[$item["UserGroupID"]];
                return $item;
            }, $users);

            $this->groups = $userGroups;
        } else {
            $this->users = array();
            $this->groups = array();
        }
    }

    /**
     * Generates the document based on a view
     * and the data passed to the constructor.
     */
    function generateDocument()
    {
        include $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Views/DashboardView.php";
    }
}