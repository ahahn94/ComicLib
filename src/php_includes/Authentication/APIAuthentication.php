<?php
/**
 * Created by ahahn94
 * on 24.05.19
 */

/**
 * Class APIAuthentication
 * Implements functions for the authentication to the ComicLib API.
 */
class APIAuthentication
{

    private $APIKeyLength = 64; // API key length in bytes. The resulting key will contain $APIKeyLength * 2 characters.

    /**
     * APIAuthentication constructor.
     */
    public function __construct()
    {
    }

    /**
     * Generate an unique API key.
     * @return bool|string New API key if successful, else boolean false.
     */
    public function generateAPIKey()
    {
        try {
            // Generate random bytes for the key.
            $bytes = random_bytes(64);

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
}