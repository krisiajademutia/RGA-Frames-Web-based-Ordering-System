<?php
interface OptionRepositoryInterface {
    public function create(array $data, array $files): bool;
    public function getAll();
}