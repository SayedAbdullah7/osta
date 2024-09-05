<?php

namespace App\Services;

use App\Repositories\LevelRepository;

class LevelService
{
    protected $levelRepository;

    public function __construct(LevelRepository $levelRepository)
    {
        $this->levelRepository = $levelRepository;
    }
    public function getAllLevels(): \Illuminate\Database\Eloquent\Collection
    {
        return $this->levelRepository->getAllLevels();
    }
    public function getLevelInformation($levelId)
    {
        return $this->levelRepository->getLevelById($levelId);
    }

    public function getNextLevel($currentLevelId)
    {
        return $this->levelRepository->getNextLevel($currentLevelId);
    }
}
