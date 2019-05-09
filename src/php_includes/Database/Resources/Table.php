<?php
/**
 * Created by ahahn94
 * on 05.05.19
 */

/**
 * Interface Table
 * Provides the basic function headers for database tables.
 */
interface Table
{
    /**
     * Get the dataset identified by the provided ID.
     * @param $id string ID of the dataset.
     * @return array Dataset. Empty array if not found.
     */
    public function get($id);

    /**
     * Get all datasets of the table.
     * @return array All datasets as an array of arrays. Empty array if not found.
     */
    public function getAll();

    /**
     * Add a new dataset to the table.
     * @param $dataset array New dataset.
     * @return int 0 if ok, else MySQL error code.
     */
    public function add($dataset);

    /**
     * Update a dataset.
     * @param $dataset array Dataset with the updated data and the ID of the dataset to change.
     * @return int 0 if ok, else MySQL error code.
     */
    public function update($dataset);

    /**
     * Remove the dataset identified by the provided ID.
     * @param $id string ID of the dataset to delete.
     * @return int 0 if ok, else MySQL error code.
     */
    public function remove($id);

}