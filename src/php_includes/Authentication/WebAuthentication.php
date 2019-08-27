<?php
/**
 * Created by ahahn94
 * on 24.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Logging/Logging.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Users.php";

/**
 * Class WebAuthentication
 * Implements functions for the authentication to the web app.
 */
class WebAuthentication
{

    private $SessionTimeout = 120;  // Timeout of the login in minutes.

    private $UsersRepo = null;

    /**
     * WebAuthentication constructor.
     */
    public function __construct()
    {
        $this->UsersRepo = new Users();
    }

    /**
     * Hash a password for saving to the database.
     * Uses password_hash() with BCrypt.
     * @param $password string Password to hash.
     * @return bool|string Hashed password if successful, else boolean false.
     */
    public static function hashPassword($password)
    {
        $hash = password_hash($password, PASSWORD_DEFAULT);
        if ($hash === false) {
            // Failed to hash password. Log error.
            Logging::logError("Failed to hash password!");
        }
        return $hash;
    }

    /**
     * Verify a password against a password hash from the Users table.
     * Uses password_verify() with BCrypt.
     * @param $password string Password to verify.
     * @param $hash string Hashed Password from the Users table.
     * @return bool true if match, else false.
     */
    public static function verifyPassword($password, $hash)
    {
        return password_verify($password, $hash);
    }

    /**
     * Log in to the web app.
     * Saves the user name and group id to the session array if
     * the correct user/password combination is provided.
     * Grants access if the user name is set inside the session array.
     * Denies access if the session timeout has been reached.
     * @return bool|string boolean true if successfully logged in, else error text.
     */
    public function logIn()
    {
        if (empty($_SESSION["User"]["Name"])) {
            // Not already logged in.
            if (!empty($userName = $_POST["username"]) && !empty($password = $_POST["password"])) {
                // Credentials were sent with request. Validate.
                if (!empty($user = $this->UsersRepo->getByName($userName))) {
                    // User exists. Validate password.
                    $hashFromDB = $user["HashedPassword"];
                    if (WebAuthentication::verifyPassword($password, $hashFromDB)) {
                        // Password is valid. Authorize.
                        $_SESSION["User"]["Name"] = $user["Name"];
                        $_SESSION["User"]["UserID"] = $user["UserID"];
                        $_SESSION["User"]["UserGroupID"] = $user["UserGroupID"];
                        // Set LastLogin on database.
                        $user["LastLogin"] = gmdate("Y-m-d H:i:s");
                        $this->UsersRepo->update($user);
                        return true;
                    } else {
                        // Password is invalid.
                        return "Incorrect username or password.";
                    }
                } else {
                    // No such User.
                    return "Incorrect username or password.";
                }
            } else {
                // Not already logged in and no valid credentials were sent.
                // Return empty string so the warning element on LoginView will not show.
                return "";
            }
        } else {
            // Name is already set in session -> already logged in.
            // Check if session timeout has been reached.
            $user = $this->UsersRepo->getByName($_SESSION["User"]["Name"]);
            if (!empty($user)) {
                // Check timeout.
                $diff = 0;
                try {
                    $db = (new DateTime($user["LastLogin"]))->getTimestamp();
                    $now = (new DateTime(date("Y-m-d H:i:s")))->getTimestamp();
                    $diff = abs($now - $db) / 60;   // Difference between timestamp from database and now (minutes)
                } catch (Exception $e) {
                    Logging::logError("Error converting timestamps for session timeout: " . $e->getMessage());
                }
                if ($diff > $this->SessionTimeout) {
                    // Session timed out. Require new login.
                    $this->logOut();
                    return "Session timed out.";
                }
                return true;
            } else {
                // User was not found. Logout.
                $this->logOut();
                return "Incorrect username or password.";
            }
        }
    }

    /**
     * Log out from the web app.
     * Will clear the session array and delete the session cookie.
     */
    public function logOut()
    {
        // Delete session variables.
        $_SESSION = array();
        // Expire cookie.
        if (ini_get("session.use_cookies")) {
            $cookieParameters = session_get_cookie_params();
            setcookie(session_name(), "", time() - 42000,
                $cookieParameters["path"], $cookieParameters["domain"],
                $cookieParameters["secure"], $cookieParameters["httponly"]
            );
        }
        // Destroy the session.
        session_destroy();
    }

}