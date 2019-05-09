<?php
/**
 * Created by ahahn94
 * on 05.05.19
 */

/**
 * Interface View
 * Provides the basic function headers for database views.
 */
interface View
{
    /**
     * Get the datasets identified by the provided ID.
     * This returns an array of the datasets, as the view groups multiple datasets under one ID.
     * @param $id string Shared ID of the group of datasets.
     * @return array Datasets. Empty array if not found.
     */
    public function getSelection($id);

    /**
     * Get all datasets of the view.
     * @return array All datasets as an array of arrays. Empty array if not found.
     */
    public function getAll();

}