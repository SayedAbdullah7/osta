<?php

namespace App\Services;
use App\Repositories\ProviderStatisticRepository as ProviderStatisticsRepository;
class ProviderStatisticService
{
    // Add service methods
    protected $providerStatisticsRepository;

    public function __construct(ProviderStatisticsRepository $providerStatisticsRepository)
    {
        $this->providerStatisticsRepository = $providerStatisticsRepository;
    }

    public function getProviderStatistics($providerId)
    {
        return $this->providerStatisticsRepository->getCurrentMonthStatistics($providerId);
    }

    public function handleOrderCompletion($providerId): void
    {
        $statistics = $this->providerStatisticsRepository->getCurrentMonthStatistics($providerId);
        $this->providerStatisticsRepository->incrementOrderCount($statistics);
    }
    public function recalculateProviderLevel($providerId): void
    {
        $statistics = $this->providerStatisticsRepository->getCurrentMonthStatistics($providerId);
//        $this->providerStatisticsRepository->recalculateLevel($statistics);
    }

    public function recalculateProviderOrdersDoneCount($providerId): void
    {
        $this->providerStatisticsRepository->recalculateOrdersDoneCount($providerId);
    }

}
