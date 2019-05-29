<?php
/**
 * Created by ahahn94
 * on 24.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Users.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Authentication/WebAuthentication.php";

/**
 * Class APIAuthentication
 * Implements functions for the authentication to the ComicLib API.
 */
class APIAuthentication
{

    private $APIKeyLength = 64; // API key length in bytes. The resulting key will contain $APIKeyLength * 2 characters.

    private $UsersRepo = null;
    private $WebAuthentication = null;

    private $AuthenticatedUser = false;     // Contains the dataset of the authenticated user after login.

    /**
     * APIAuthentication constructor.
     */
    public function __construct()
    {
        $this->UsersRepo = new Users();
        $this->WebAuthentication = new WebAuthentication();
    }

    /**
     * Generate an unique API key.
     * @return bool|string New API key if successful, else boolean false.
     */
    public function generateAPIKey()
    {
        try {
            // Generate random bytes for the key.
            $bytes = random_bytes($this->APIKeyLength);

            // Turn random bytes into an upper case hex string with $APIKeyLength * 2 characters.
            $hex = bin2hex($bytes);
            $apiKey = strtoupper($hex);
            return $apiKey;
        } catch (Exception $e) {
            // Log error.
            Logging::logError("Failed to generate APIKey! " . $e->getMessage());
        }
        return false;
    }

    /**
     * Check HTTP Basic authorization.
     * @return bool true if successful, else false.
     */
    public function basicAuthentication()
    {
        /*
         * Get authorization and check type.
         */
        $authorization = getallheaders()["Authorization"];
        if (empty($authorization)) return false;    // Authorization header not set.
        // Split into authorization type and content.
        $authorizationParts = explode(" ", $authorization);
        $authorizationType = $authorizationParts[0];
        $authorizationContent = $authorizationParts[1];
        if ($authorizationType !== "Basic") return false;   // Wrong authorization type.

        /*
         * Convert Base64 string into username and password.
         */
        // Convert from Base64.
        $clearText = base64_decode($authorizationContent);
        if ($clearText === false) return false; // Conversion failed.
        if (strpos($clearText, ":") === 0) return false;    // No valid Basic Auth string (username:password).
        // Split into user name and password.
        $credentials = explode(":", $clearText);
        $userName = $credentials[0];
        $password = implode(":", array_slice($credentials, 1)); // Password may contain ":", so use as glue.

        /*
         * Validate credentials against database.
         * Return APIKey if valid.
         */
        if ($this->validateCredentials($userName, $password)) {
            if (!empty($user = $this->UsersRepo->getByName($userName))) {
                // Successfully read user from database. Return true.
                $this->AuthenticatedUser = $user;   // Set authenticated user.
                return true;
            }
        }
        return false;
    }

    /**
     * Check Bearer Token authorization.
     * @return bool true if successful, else false.
     */
    public function bearerTokenAuthentication()
    {
        /*
         * Get authorization and check type.
         */
        $authorization = getallheaders()["Authorization"];
        if (empty($authorization)) return false;    // Authorization header not set.
        // Split into authorization type and content.
        $authorizationParts = explode(" ", $authorization);
        $authorizationType = $authorizationParts[0];
        $authorizationContent = $authorizationParts[1];
        if ($authorizationType !== "Bearer") return false;   // Wrong authorization type.

        // Validate APIkey.
        $valid = $this->validateAPIKey($authorizationContent);
        return $valid;
    }

    /**
     * Validate credentials against the Users table.
     * @param $userName string Name of the user.
     * @param $password string Password of the user.
     * @return bool true if valid, else false.
     */
    private function validateCredentials($userName, $password)
    {

        if (!empty($userName) && !empty($password)) {
            // Credentials are not empty. Validate.
            if (!empty($user = $this->UsersRepo->getByName($userName))) {
                // User exists. Validate password.
                $hashFromDB = $user["HashedPassword"];
                if ($this->WebAuthentication->verifyPassword($password, $hashFromDB)) {
                    // Password is valid. Return true.
                    return true;
                } else {
                    // Password is invalid. Return false.
                    return false;
                }
            } else {
                // No such user. Return false.
                return false;
            }
        } else {
            // Credentials are empty. Return false.
            return false;
        }

    }

    /**
     * Validate an APIKey against the Users table of the database.
     * @param $apiKey string APIKey to validate.
     * @return boolean true if valid, else false.
     */
    private function validateAPIKey($apiKey)
    {
        // Get user from database.
        $user = $this->UsersRepo->getByAPIKey($apiKey);
        // If no user was found ($user is empty), return false, else true.
        if (!empty($user)) {
            // Set authenticated user.
            $this->AuthenticatedUser = $user;
        }
        return !empty($user);
    }

    /*
     * Getters.
     */

    public function getAuthenticatedUser()
    {
        return $this->AuthenticatedUser;
    }


}