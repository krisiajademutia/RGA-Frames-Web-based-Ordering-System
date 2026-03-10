<?php
interface OptionRepositoryInterface {
    public function create(array $data, array $files): bool;
    public function getAll();
    public function update(int $id, array $data, array $files = []): bool;
    public function delete(int $id): bool;
}