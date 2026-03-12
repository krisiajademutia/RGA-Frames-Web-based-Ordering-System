<?php

class OptionService {
    private $repositories = [];

    /**
     * Registers a repository for a specific option type (e.g., 'frame_types')
     */
    public function registerRepository($key, OptionRepositoryInterface $repo) {
        $this->repositories[$key] = $repo;
    }

    /**
     * Adds a new option using the corresponding repository
     */
    public function addOption($key, array $data, array $files): bool {
        return isset($this->repositories[$key]) ? $this->repositories[$key]->create($data, $files) : false;
    }

    /**
     * Fetches all options for a specific tab
     */
    public function fetchOptions($key) {
        return isset($this->repositories[$key]) ? $this->repositories[$key]->getAll() : null;
    }

    /**
     * NEW: Fetches a single option by ID
     * Fixed the Fatal Error by ensuring this method exists and has a safety check
     */
    public function getOptionById($key, int $id): ?array {
        if (isset($this->repositories[$key])) {
            return $this->repositories[$key]->getById($id);
        }
        return null;
    }

    /**
     * Updates an existing option
     */
    public function updateOption($key, int $id, array $data, array $files = []): bool {
        return isset($this->repositories[$key]) ? $this->repositories[$key]->update($id, $data, $files) : false;
    }

    /**
     * Deletes an option
     */
    public function deleteOption($key, int $id): bool {
        return isset($this->repositories[$key]) ? $this->repositories[$key]->delete($id) : false;
    }
}