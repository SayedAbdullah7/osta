<?php

namespace App\Repositories\Interfaces;

namespace App\Repositories\Interfaces;

use App\Models\Order;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;

interface OrderRepositoryInterface
{
    public function getOrdersForUser(User $user): Collection;
    public function store(array $data, User $user): Order;
}
