<?php
/**
 * Created by ahahn94
 * on 28.05.19
 */

require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/IssueReadStatus.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/VolumeReadStatus.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/Publishers.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/PublisherVolumes.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Database/Resources/ReadStatus.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Caching/ImageCache.php";
require_once $_SERVER["DOCUMENT_ROOT"] . "/php_includes/Storage/StorageManager.php";

/**
 * Class V1Repo
 * Implements functions for accessing and formatting data from the server.
 */
class V1Repo
{

    /*
     * Database repos.
     */
    private $IssueReadStatusRepo = null;
    private $VolumeReadStatusRepo = null;
    private $PublisherVolumesRepo = null;
    private $PublishersRepo = null;
    private $ReadStatusRepo = null;

    /**
     * V1Repo constructor.
     */
    public function __construct()
    {
        $this->IssueReadStatusRepo = new IssueReadStatus();
        $this->VolumeReadStatusRepo = new VolumeReadStatus();
        $this->PublisherVolumesRepo = new PublisherVolumes();
        $this->PublishersRepo = new Publishers();
        $this->ReadStatusRepo = new ReadStatus();
    }

    /**
     * Get all issues incl. ReadStatus.
     * Preformatted for JSON.
     * @param $userID string UserID of the user requesting the issues. Determines the ReadStatus.
     * @return array Issues as an array of preformatted issues. Empty array if none found.
     */
    public function getIssues($userID): array
    {
        $issues = $this->IssueReadStatusRepo->getAll($userID);
        if (!empty($issues)) {
            // Issues found. Format issues and return them.
            $issues = array_map(function ($item) {
                return $this->formatIssue($item);
            }, $issues);
            return $issues;
        } else {
            // No issues found. Return empty array.
            return array();
        }
    }

    /**
     * Get all volumes incl. ReadStatus.
     * Preformatted for JSON.
     * @param $userID string UserID of the user requesting the volumes. Determines the ReadStatus.
     * @return array Volumes as an array of preformatted volumes. Empty array if none found.
     */
    public function getVolumes($userID): array
    {
        $volumes = $this->VolumeReadStatusRepo->getAll($userID);
        if (!empty($volumes)) {
            // Volumes found. Format volumes and return them.
            $volumes = array_map(function ($item) {
                return $this->formatVolume($item);
            }, $volumes);
            return $volumes;
        } else {
            // No volumes found. Return empty array.
            return array();
        }
    }

    /**
     * Get all publishers.
     * Preformatted for JSON.
     * @return array Publishers as an array of preformatted publishers. Empty array if none found.
     */
    public function getPublishers(): array
    {
        $publishers = $this->PublishersRepo->getAll();
        if (!empty($publishers)) {
            // Publishers found. Format publishers and return them.
            $publishers = array_map(function ($item) {
                return $this->formatPublisher($item);
            }, $publishers);
            return $publishers;
        } else {
            // No publishers found. Return empty array.
            return array();
        }
    }

    /**
     * Get a single issue incl. ReadStatus.
     * Preformatted for JSON.
     * @param $userID string UserID of the user requesting the issue. Determines the ReadStatus.
     * @param $issueID string IssueID of the requested issue.
     * @return array Issue as a preformatted array. Empty array if not found.
     */
    public function getIssue($userID, $issueID): array
    {
        $issue = $this->IssueReadStatusRepo->getSingleDataset($issueID, $userID);
        if (!empty($issue)) {
            // Issue found. Format issue and return it.
            $formattedIssue = $this->formatIssue($issue);
            return $formattedIssue;
        } else {
            // No issue found. Return empty array.
            return array();
        }
    }

    /**
     * Get a single volume incl. ReadStatus.
     * Preformatted for JSON.
     * @param $userID string UserID of the user requesting the volume. Determines the ReadStatus.
     * @param $volumeID string VolumeID of the requested volume.
     * @return array Volume as a preformatted array. Empty array if not found.
     */
    public function getVolume($userID, $volumeID): array
    {
        $volume = $this->VolumeReadStatusRepo->getSingleDataset($volumeID, $userID);
        if (!empty($volume)) {
            // Volume found. Format volume and return it.
            $formattedVolume = $this->formatVolume($volume);
            return $formattedVolume;
        } else {
            // No volume found. Return empty array.
            return array();
        }
    }

    /**
     * Get a single publisher incl. ReadStatus.
     * Preformatted for JSON.
     * @param $publisherID string PublisherID of the requested publisher.
     * @return array Publisher as a preformatted array. Empty array if not found.
     */
    public function getPublisher($publisherID): array
    {
        $publisher = $this->PublishersRepo->get($publisherID);
        if (!empty($publisher)) {
            // Publisher found. Format publisher and return it.
            $formattedPublisher = $this->formatPublisher($publisher);
            return $formattedPublisher;
        } else {
            // No publisher found. Return empty array.
            return array();
        }
    }

    /**
     * Get all issues of a single volume incl. ReadStatus.
     * Preformatted for JSON.
     * @param $userID string UserID of the user requesting the volume issues. Determines the ReadStatus.
     * @param $volumeID string VolumeID of the requested volume issues.
     * @return array Volume issues as an array of preformatted issues. Empty array if not found.
     */
    public function getVolumeIssues($userID, $volumeID): array
    {
        $issues = $this->IssueReadStatusRepo->getSelection($volumeID, $userID);
        if (!empty($issues)) {
            // Issues found. Format issues and return them.
            $issues = array_map(function ($item) {
                return $this->formatIssue($item);
            }, $issues);
            return $issues;
        } else {
            // No issues found. Return empty array.
            return array();
        }
    }

    /**
     * Get all volumes of a single publisher incl. ReadStatus.
     * Preformatted for JSON.
     * @param $userID string UserID of the user requesting the publisher volumes. Determines the ReadStatus.
     * @param $publisherID string PublisherID of the requested publisher volumes.
     * @return array Publisher volumes as an array of preformatted volumes. Empty array if not found.
     */
    public function getPublisherVolumes($userID, $publisherID): array
    {
        $issues = $this->VolumeReadStatusRepo->getSelection($publisherID, $userID);
        if (!empty($issues)) {
            // Publisher volumes found. Format volumes and return them.
            $issues = array_map(function ($item) {
                return $this->formatVolume($item);
            }, $issues);
            return $issues;
        } else {
            // No volumes found. Return empty array.
            return array();
        }
    }

    /**
     * Get the ReadStatus of a single issue.
     * Preformatted for JSON.
     * @param $userID string UserID of the user requesting the issue ReadStatus. Determines the ReadStatus.
     * @param $issueID string IssueID of the requested ReadStatus.
     * @return array ReadStatus as a preformatted array. Empty array if not found.
     */
    public function getIssueReadStatus($userID, $issueID)
    {
        $readStatus = $this->IssueReadStatusRepo->getSingleDataset($issueID, $userID);
        if (!empty($readStatus)) {
            // Remove all unnecessary data from dataset before return.
            $filteredReadStatus = array("IssueID" => $readStatus["IssueID"], "IsRead" => $readStatus["IsRead"],
                "CurrentPage" => $readStatus["CurrentPage"], "Changed" => $readStatus["Changed"]);
            return $filteredReadStatus;
        } else {
            // ReadStatus not found. Return empty array.
            return array();
        }
    }

    /**
     * Get the ReadStatus of a single volume.
     * Preformatted for JSON.
     * @param $userID string UserID of the user requesting the volume ReadStatus. Determines the ReadStatus.
     * @param $volumeID string VolumeID of the requested ReadStatus.
     * @return array ReadStatus as a preformatted array. Empty array if not found.
     */
    public function getVolumeReadStatus($userID, $volumeID)
    {
        $readStatus = $this->VolumeReadStatusRepo->getSingleDataset($volumeID, $userID);
        if (!empty($readStatus)) {
            // Remove all unnecessary data from dataset before return.
            $filteredReadStatus = array("VolumeID" => $readStatus["VolumeID"], "IsRead" => $readStatus["IsRead"], "Changed" => $readStatus["Changed"]);
            return $filteredReadStatus;
        } else {
            // ReadStatus not found. Return empty array.
            return array();
        }
    }

    /**
     * Update the ReadStatus for a single issue.
     * @param $userID string UserID of the user to update the ReadStatus for.
     * @param $issueID string IssueID of the issue to update the ReadStatus for.
     * @param $dataset array Dataset containing the new IsRead (bool), CurrentPage (int) and Changed (datetime).
     * @return array The new ReadStatus if update successful, else empty array.
     */
    public function setIssueReadStatus($userID, $issueID, $dataset)
    {
        $this->ReadStatusRepo->updateIssue($issueID, $userID, $dataset);
        // Will be empty and no rows will have changed if $issueID was not valid.
        return $this->getIssueReadStatus($userID, $issueID);
    }

    /**
     * Update the ReadStatus for a single volume and its issues.
     * @param $userID string UserID of the user to update the ReadStatus for.
     * @param $volumeID string VolumeID of the volume and issues to update the ReadStatus for.
     * @param $readStatus boolean New ReadStatus. CurrentPage on the issues ReadStatuses will default to 0.
     * @param $changed Datetime of the change.
     * @return array The new ReadStatus if update successful, else empty array.
     */
    public function setVolumeReadStatus($userID, $volumeID, $readStatus, $changed)
    {
        $this->ReadStatusRepo->updateVolume($volumeID, $userID, $readStatus, $changed);
        // Will be empty and no rows will have changed if $volumeID was not valid.
        return $this->getVolumeReadStatus($userID, $volumeID);
    }

    /**
     * Format an issue dataset for the API.
     * @param $issue array Issue dataset to format.
     * @return array Formatted issue dataset, empty array if $issue was empty.
     */
    private function formatIssue($issue): array
    {
        if (!empty($issue)) {
            // $issue has content. Format.
            $formattedIssue = array(
                "ID" => $issue["IssueID"],
                "Link" => APIGenerics::getAPIPathV1() . "issues/" . $issue["IssueID"],
                "Description" => $issue["Description"],
                "ImageFileURL" => ImageCache::getImageCachePath() . $issue["ImageFileName"],
                "File" => array(
                    "FileName" => $issue["IssueLocalPath"],
                    "FileURL" => APIGenerics::getAPIPathV1() . "issues/" . $issue["IssueID"] . "/file"
                ),
                "Number" => $issue["IssueNumber"],
                "Name" => $issue["Name"],
                "ReadStatus" => array(
                    "IsRead" => $issue["IsRead"],
                    "CurrentPage" => $issue["CurrentPage"],
                    "Link" => APIGenerics::getAPIPathV1() . "issues/" . $issue["IssueID"] . "/readstatus"
                ),
                "Volume" => array(
                    "VolumeID" => $issue["VolumeID"],
                    "Link" => APIGenerics::getAPIPathV1() . "volumes/" . $issue["VolumeID"]
                )
            );
            return $formattedIssue;
        } else {
            // $issue has no content. Return empty array.
            return array();
        }
    }

    /**
     * Format a volume dataset for the API.
     * @param $volume array Volume dataset to format.
     * @return array Formatted volume dataset, empty array if $volume was empty.
     */
    private function formatVolume($volume): array
    {
        if (!empty($volume)) {
            // $volume has content. Format.
            $formattedIssue = array(
                "ID" => $volume["VolumeID"],
                "Link" => APIGenerics::getAPIPathV1() . "volumes/" . $volume["VolumeID"],
                "Description" => $volume["Description"],
                "ImageFileURL" => ImageCache::getImageCachePath() . $volume["ImageFileName"],
                "Name" => $volume["Name"],
                "StartYear" => $volume["StartYear"],
                "IssuesURL" => APIGenerics::getAPIPathV1() . "volumes/" . $volume["VolumeID"] . "/issues",
                "IssueCount" => $volume["IssueCount"],
                "ReadStatus" => array(
                    "IsRead" => $volume["IsRead"],
                    "Link" => APIGenerics::getAPIPathV1() . "volumes/" . $volume["VolumeID"] . "/readstatus"
                ),
                "Publisher" => array(
                    "PublisherID" => $volume["PublisherID"],
                    "Link" => APIGenerics::getAPIPathV1() . "publishers/" . $volume["PublisherID"]
                )
            );
            return $formattedIssue;
        } else {
            // $volume has no content. Return empty array.
            return array();
        }
    }

    /**
     * Format a publisher dataset for the API.
     * @param $publisher array Publisher dataset to format.
     * @return array Formatted publisher dataset, empty array if $publisher was empty.
     */
    private function formatPublisher($publisher): array
    {
        if (!empty($publisher)) {
            // $publisher has content. Format.
            $formattedIssue = array(
                "ID" => $publisher["PublisherID"],
                "Link" => APIGenerics::getAPIPathV1() . "publishers/" . $publisher["PublisherID"],
                "Description" => $publisher["Description"],
                "ImageFileURL" => ImageCache::getImageCachePath() . $publisher["ImageFileName"],
                "Name" => $publisher["Name"],
                "VolumesURL" => APIGenerics::getAPIPathV1() . "publishers/" . $publisher["PublisherID"] . "/volumes",
                "VolumesCount" => $publisher["VolumesCount"],
            );
            return $formattedIssue;
        } else {
            // $publisher has no content. Return empty array.
            return array();
        }
    }

    /**
     * Send the comic file of an issue for client-side download.
     * Sends file on success and 404 - Not Found on fail.
     * @param $userID string UserID of the user requesting the download.
     * @param $issueID string IssueID of the requested comic file.
     */
    public function downloadIssue($userID, $issueID)
    {
        $issue = $this->IssueReadStatusRepo->getSingleDataset($issueID, $userID);
        if (!empty($issue)) {
            // Issue found. Build path and send file.
            $volume = $this->VolumeReadStatusRepo->getSingleDataset($issue["VolumeID"], $userID);
            $filePath = StorageManager::getStoragePath() . "/" . $volume["VolumeLocalPath"] . "/" .
                $issue["IssueLocalPath"];
            // Try to send file. If it can not be found, send 404 - Not Found.
            if ($this->download($filePath) === false) APIGenerics::sendNotFound();
        } else {
            // Issue not found. Send 404 - Not Found.
            APIGenerics::sendNotFound();
        }
    }

    /**
     * Start download of the specified file from the ComicLib API.
     * Uses XSendFile to enable downloads from the otherwise locked storage directory.
     * storage is locked via .htaccess to require a login before downloading comics.
     * @param $filePath string Path to the file to download.
     * @return boolean false if file not found.
     */
    private function download($filePath)
    {
        if (file_exists($filePath)) {
            $fileName = basename($filePath);    // Get filename from filepath.

            /*
             * Set and send headers for the download.
             */

            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $fileName);
            header('X-Sendfile: ' . $filePath);
            exit;

        } else {
            // File does not exist. Return false.
            return false;
        }
    }
}