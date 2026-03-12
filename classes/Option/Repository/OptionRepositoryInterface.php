<?php
interface OptionRepositoryInterface {
    /**
     * Fetch a single record by its primary key
     */
    public function getById(int $id): ?array;

    /**
     * Create a new record
     */
    public function create(array $data, array $files): bool;

    /**
     * Fetch all records for the current table
     */
    public function getAll();

    /**
     * Update an existing record
     */
    public function update(int $id, array $data, array $files = []): bool;

    /**
     * Delete a record and its associated files
     */
    public function delete(int $id): bool;
}