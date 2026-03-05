<?php
namespace Classes\Frames;

use Classes\Frames\Repository\FrameRepositoryInterface;

class FrameService {
    private $repository;

    public function __construct(FrameRepositoryInterface $repository) {
        $this->repository = $repository;
    }

    public function getAllFrames() {
        return $this->repository->getAll();
    }

    public function getFrameById(int $id) {
        return $this->repository->getById($id);
    }

    public function createFrame(array $data) {
        return $this->repository->create($data);
    }

    public function updateFrame(int $id, array $data) {
        return $this->repository->update($id, $data);
    }

    public function deleteFrame(int $id) {
        return $this->repository->delete($id);
    }
}