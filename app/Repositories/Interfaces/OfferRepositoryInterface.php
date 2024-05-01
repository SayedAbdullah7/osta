<?php

namespace App\Repositories\Interfaces;

use App\Models\Offer;
use App\Models\User;

interface OfferRepositoryInterface
{
    public function getOffersForOrder($orderId, User $user): array;
    public function acceptOffer($offerId, User $user): Offer;
    public function rejectOffer($offerId, User $user): Offer;
}
