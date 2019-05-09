<?php
/**
 * Created by ahahn94
 * on 06.05.19
 */

/**
 * Interface APIResource
 * Provides the basic function headers for accessing the resources of the ComicVine API.
 */
interface APIResource
{
    /**
     * Get the resources item identified by ID from the ComicVine API.
     * @param $id string ID of the resource item to get.
     * @return array JSON array with the data of the item.
     */
    public static function get($id);

    /**
     * Turn a JSON string returned by the API into an object that fits to the database table.
     * @param $jsonString string JSON string returned by the ComicVine API.
     * @return array Array containing only the fields needed from the API for the database.
     */
    static function convertToObject($jsonString);
}