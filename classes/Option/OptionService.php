<?php
class OptionService {
    private $repositories = [];

    public function registerRepository($key, OptionRepositoryInterface $repo) {
        $this->repositories[$key] = $repo;
    }

    public function addOption($key, array $data, array $files): bool {
        return isset($this->repositories[$key]) ? $this->repositories[$key]->create($data, $files) : false;
    }

    public function fetchOptions($key) {
        return isset($this->repositories[$key]) ? $this->repositories[$key]->getAll() : null;
    }
}