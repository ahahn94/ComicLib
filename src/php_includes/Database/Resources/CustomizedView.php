<?php
/**
 * Created by ahahn94
 * on 27.05.19
 */

/**
 * Interface CustomizedView
 * Provides the basic function headers for customized database views.
 * These views provide content that is customized for each user.
 */
interface CustomizedView
{
    /**
     * Get the datasets identified by the provided $userID.
     * This returns an array of the datasets, as the view groups multiple datasets under one userID.
     * @param $userID string UserID of the group of datasets.
     * @return array Datasets. Empty array if not found or error.
     */
    public function getAll($userID);

    /**
     * Get the datasets identified by the provided $id and $userID.
     * @param $id string Shared ID of the group of datasets.
     * @param $userID string UserID of the user to customize the datasets for.
     * @return array Datasets. Empty array if not found or error.
     */
    public function getSelection($id, $userID);

    /**
     * Get the dataset identified by the provided $id and $userID.
     * @param $id string ID of the dataset.
     * @param $userID string UserID of the user to customize the dataset for.
     * @return array Dataset. Empty array if not found or error.
     */
    public function getSingleDataset($id, $userID);

}