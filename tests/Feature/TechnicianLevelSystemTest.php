<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Provider;
use App\Models\Level;
use App\Models\Order;
use App\Models\Review;
use App\Models\Subscription;
use App\Models\ProviderSubscription;
use App\Services\MetricUpdateService;
use App\Services\LevelEvaluationService;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use App\Events\ProviderLevelPromoted;
use Carbon\Carbon;
use DB;

class TechnicianLevelSystemTest extends TestCase
{
    use RefreshDatabase;

    protected $metricService;
    protected $levelService;
    protected $walletService;
    protected $levels;

    protected function setUp(): void
    {
        parent::setUp();

        // Setup Services
        $this->metricService = app(MetricUpdateService::class);
        $this->levelService = app(LevelEvaluationService::class);
        $this->walletService = app(WalletService::class);

        // Seed Levels (As per new logical order)
        // Bronze: Easiest (Default)
        $this->levels['bronze'] = Level::create([
            'name' => 'Bronze', 'slug' => 'bronze', 'level' => 1,
            'requirements' => ['metrics' => ['completed_orders' => 20, 'average_rating' => 4.0], 'duration' => 'P1M'],
            'benefits' => ['commission_rate' => 0.80], // Platform takes 20%
            'is_active' => true
        ]);

        // Silver
        $this->levels['silver'] = Level::create([
            'name' => 'Silver', 'slug' => 'silver', 'level' => 2,
            'requirements' => ['metrics' => ['completed_orders' => 50, 'average_rating' => 4.3], 'duration' => 'P1M'],
            'benefits' => ['commission_rate' => 0.85], // Platform takes 15%
            'is_active' => true
        ]);

        // Gold: Hardest
        $this->levels['gold'] = Level::create([
            'name' => 'Gold', 'slug' => 'gold', 'level' => 3,
            'requirements' => ['metrics' => ['completed_orders' => 100, 'average_rating' => 4.5], 'duration' => 'P1M'],
            'benefits' => ['commission_rate' => 0.90], // Platform takes 10%
            'is_active' => true
        ]);

        // Create User & Provider
        $user = User::factory()->create();
        $this->provider = Provider::create(['user_id' => $user->id, 'is_active' => true]);

        // Initialize Provider Level to Bronze
        $this->provider->levels()->attach($this->levels['bronze']->id, [
            'is_current' => true,
            'achieved_at' => now(),
            'valid_until' => now()->addMonth()
        ]);
    }

    /** @test */
    public function it_updates_metrics_correctly_when_order_is_completed()
    {
        // 1. Initial State
        $this->metricService->updateOrderMetrics($this->provider, 5.0);

        $this->assertDatabaseHas('provider_metrics', [
            'provider_id' => $this->provider->id,
            'completed_orders' => 1,
            'average_rating' => 5.00
        ]);

        // 2. Add another order with 4.0 rating
        $this->metricService->updateOrderMetrics($this->provider, 4.0);

        $this->assertDatabaseHas('provider_metrics', [
            'provider_id' => $this->provider->id,
            'completed_orders' => 2,
            'average_rating' => 4.50 // (5+4)/2
        ]);
    }

    /** @test */
    public function it_updates_rating_only_without_incrementing_orders()
    {
        // 1. Complete one order with 5 stars
        $this->metricService->updateOrderMetrics($this->provider, 5.0); // Count: 1, Avg: 5.0

        // 2. Add a review separately (e.g. user updated review or added it later)
        // Logic: The system should update the rating but NOT increment completed_orders
        // Let's say we update the rating for that existing order to 3.0 (or add a new rating for an existing order)
        // Wait, updateRatingMetrics logic assumes we are adding a NEW rating to the pool, but for an order that was ALREADY counted in completed_orders.

        // Simulate: Order was done (count=1), but rating came later.
        // First, let's reset metrics to: 1 order, 0 rating (if that was possible) or just verify logic.

        // Let's try: Existing: 10 orders, 5.0 avg.
        // New Rating incoming: 1.0 (for one of those 10 orders).
        // New Avg should be: ((5.0 * 10) + 1.0) / 10?? No, that's adding a rating.
        // The Service logic is: new_avg = ((old_avg * old_count) + new_rating) / (old_count + 1)
        // This implies this is a NEW rating instance.

        $this->metricService->updateRatingMetrics($this->provider, 1.0);

        // Expectation: Orders still 1, but Avg Rating considers 2 ratings now?
        // Based on code: incrementOrders=false.
        // Denominator = max(count + 1, 1) = 2.
        // Numerator = (5.0 * 1) + 1.0 = 6.0.
        // Result = 3.0.

        $metrics = $this->provider->currentMonthMetrics;
        $this->assertEquals(1, $metrics->completed_orders); // Should NOT increase
        $this->assertEquals(3.0, $metrics->average_rating);
    }

    /** @test */
    public function it_promotes_provider_automatically_to_silver()
    {
        Event::fake([ProviderLevelPromoted::class]);

        // Target: Silver needs 50 orders, 4.3 rating

        // 1. Simulate Metrics meeting Silver requirements
        $metrics = $this->provider->currentMonthMetrics()->firstOrCreate([
            'month' => now()->startOfMonth()
        ]);
        $metrics->completed_orders = 51;
        $metrics->average_rating = 4.4;
        $metrics->save();

        // 2. Run Evaluation
        $this->levelService->evaluateProvider($this->provider);

        // 3. Assertions
        $this->assertTrue(
            $this->provider->levels()
                ->where('level_id', $this->levels['silver']->id)
                ->wherePivot('is_current', true)
                ->exists(),
            'Provider should be promoted to Silver'
        );

        Event::assertDispatched(ProviderLevelPromoted::class);
    }

    /** @test */
    public function it_applies_subscription_percentage_if_higher_than_level()
    {
        // Setup: Provider is Bronze (80% provider share)
        // Subscription: 85% provider share (fee_percentage = 85)

        // 1. Create Subscription Plan
        $subscription = Subscription::create([
            'name' => 'Pro Plan',
            'fee_percentage' => 85, // 85% for provider
            'price' => 100,
            'duration' => 30
        ]);

        // 2. Subscribe Provider
        ProviderSubscription::create([
            'provider_id' => $this->provider->id,
            'subscription_id' => $subscription->id,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'status' => 'active'
        ]);

        // 3. Calculate Percentage via WalletService (using Reflection to access private method or mocking the public flow)
        // Since getPercentage is private in WalletService, we can test the outcome via a public method if available,
        // or easier: replicate the logic here to verify the concept since we modified WalletService.
        // Ideally, we test public methods. Let's assume we can't easily call getPercentage directly.
        // We will use Reflection to test the private method `getPercentage` in WalletService.

        $reflection = new \ReflectionClass($this->walletService);
        $method = $reflection->getMethod('getPercentage');
        $method->setAccessible(true);

        // Mock Provider Statistics Service or Ensure stats exist
        // The service calls $this->providerStatisticService->getProviderStatistics($providerId)
        // We need to make sure that doesn't fail.

        // Actually, simpler approach: The logic relies on Level and Subscription.
        // Let's manually trigger the logic used in WalletService::getPercentage

        $levelPercentage = 0.80; // Bronze
        $subPercentage = 0.85; // Subscription

        // Actual execution via WalletService
        // We need to mock the dependencies of WalletService that we can't easily satisfy in a unit test
        // without a full app integration.
        // But since we are in a Feature test with RefreshDatabase, we can try invoking it.

        // We need to mock ProviderStatisticService just to return the level name.
        $this->mock(\App\Services\ProviderStatisticService::class, function ($mock) {
            $mock->shouldReceive('getProviderStatistics')
                 ->andReturn((object)['level' => 1]); // Returns level number
        });

        // Re-instantiate WalletService to get the mock
        $walletService = app(WalletService::class);
        $method = new \ReflectionClass($walletService);
        $getPercentage = $method->getMethod('getPercentage');
        $getPercentage->setAccessible(true);

        [$providerPct, $adminPct] = $getPercentage->invoke($walletService, $this->provider->id);

        $this->assertEquals(0.85, $providerPct, 'Provider should get 85% from subscription (higher than Bronze 80%)');
    }

    /** @test */
    public function it_uses_level_percentage_if_higher_than_subscription()
    {
        // Case: Provider promotes to Gold (90%) but has old subscription (85%)

        // 1. Promote to Gold
        $this->provider->levels()->updateExistingPivot($this->levels['bronze']->id, ['is_current' => false]);
        $this->provider->levels()->attach($this->levels['gold']->id, ['is_current' => true]);

        // 2. Active Subscription (85%)
        $subscription = Subscription::create([
            'name' => 'Pro Plan',
            'fee_percentage' => 85,
            'price' => 100,
            'duration' => 30
        ]);
        ProviderSubscription::create([
            'provider_id' => $this->provider->id,
            'subscription_id' => $subscription->id,
            'start_date' => now(),
            'end_date' => now()->addMonth(),
            'status' => 'active'
        ]);

        // 3. Mock dependency
        $this->mock(\App\Services\ProviderStatisticService::class, function ($mock) {
            $mock->shouldReceive('getProviderStatistics')
                 ->andReturn((object)['level' => 3]); // Gold Level
        });

        $walletService = app(WalletService::class);
        $method = new \ReflectionClass($walletService);
        $getPercentage = $method->getMethod('getPercentage');
        $getPercentage->setAccessible(true);

        [$providerPct, $adminPct] = $getPercentage->invoke($walletService, $this->provider->id);

        $this->assertEquals(0.90, $providerPct, 'Provider should get 90% from Gold Level (higher than Subscription 85%)');
    }
}
