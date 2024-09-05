<?php

namespace App\Repositories;

use App\Models\Level;

class LevelRepository
{
    public function getLevelById($levelId)
    {
        return Level::find($levelId);
    }

    public function getNextLevel($currentLevelId)
    {
        $level = Level::find($currentLevelId);
        return $level ? $level->nextLevel : null;
    }

    public function getAllLevels(): \Illuminate\Database\Eloquent\Collection
    {
        return Level::all();
    }
}
