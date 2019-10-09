<?php
/**
 * Created by ahahn94
 * on 09.10.19
 */

/**
 * Class TypeConverters
 * Implements converter functions for API datasets.
 */
class TypeConverters
{

    /**
     * Convert the data types in ReadStatus to better match the content.
     * Turns IsRead into a boolean and CurrentPage into an integer.
     * @param array $readStatus ReadStatus array of an Issue.
     * @return array Modified ReadStatus.
     */
    static function issueReadStatusConverter(array $readStatus): array {
        $readStatus["IsRead"] = $readStatus["IsRead"] === "1" ? true : false;
        $readStatus["CurrentPage"] = intval($readStatus["CurrentPage"]);
        return $readStatus;
    }

    /**
     * Convert the data types in ReadStatus to better match the content.
     * Turns IsRead into a boolean.
     * @param array $readStatus ReadStatus array of a Volume.
     * @return array Modified ReadStatus.
     */
    static function volumeReadStatusConverter(array $readStatus): array {
        $readStatus["IsRead"] = $readStatus["IsRead"] === "1" ? true : false;
        return $readStatus;
    }

    /**
     * Convert the data types in Volume to better match the content.
     * Turns IssueCount and StartYear into integers.
     * @param array $volume Volume as an array.
     * @return array Modified Volume.
     */
    static function volumeConverter(array $volume): array {
        $volume["IssueCount"] = intval($volume["IssueCount"]);
        $volume["StartYear"] = intval($volume["StartYear"]);
        return $volume;
    }

}