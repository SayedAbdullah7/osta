<?php

// app/Services/ProviderOrderService.php
namespace App\Services;


use App\Http\Resources\OfferResource;
use App\Http\Resources\OrderResource;
use App\Models\Offer;
use App\Models\Order;
use App\Models\Provider;
use App\Models\Review;
use App\Models\ReviewStatistic;
use App\Models\User;
use App\Models\UserAction;
use App\Repositories\Interfaces\OrderRepositoryInterface;
use Bavix\Wallet\Internal\Exceptions\ExceptionInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use App\Services\ProviderStatisticService;
use App\Services\MetricUpdateService;
use App\Services\LevelEvaluationService;

class ProviderOrderService
{
    private OrderRepositoryInterface $orderRepository;
    protected \App\Services\ProviderStatisticService $providerStatisticsService;
    protected MetricUpdateService $metricUpdateService;
    protected LevelEvaluationService $levelEvaluationService;

    public function __construct(
        OrderRepositoryInterface $orderRepository,
        ProviderStatisticService $providerStatisticsService,
        MetricUpdateService $metricUpdateService,
        LevelEvaluationService $levelEvaluationService
    ) {
        $this->orderRepository = $orderRepository;
        $this->providerStatisticsService = $providerStatisticsService;
        $this->metricUpdateService = $metricUpdateService;
        $this->levelEvaluationService = $levelEvaluationService;
    }


    public function updateOrderToComing($orderId, $provider): ?\App\Models\Order
    {
        $order = $this->orderRepository->find($orderId);
        if ($order && $this->orderRepository->isOrderAvailableToBeComingByProvider($order, $provider)) {
            return $this->orderRepository->updateOrderToComing($order);
        }
        return null;
    }

    public function updateOrderToAlmostDone($orderId, $provider): ?\App\Models\Order
    {
        $order = $this->orderRepository->find($orderId);
        if ($order && $this->orderRepository->isOrderAvailableToBeAlmostDoneByProvider($order, $provider)) {
            return $this->orderRepository->updateOrderToAlmostDone($order);
        }
        return null;
    }

    /**
     * @throws ExceptionInterface
     */
    public function updateOrderToDone(Request $request, $orderId, $provider): ?array
    {
        $order = $this->orderRepository->find($orderId);
        $providerId = $provider->id;
        if (!$order || !$this->orderRepository->isOrderAvailableToBeDoneByProvider($order, $provider)) {
            return null;
        }

//        if ($request->payment_method == 'cash') {
//            $payResponse = app(WalletService::class)->payCash($order);
//        } elseif ($request->payment_method == 'wallet') {
//            $payResponse = app(WalletService::class)->payByWallet($order);
//        }
        if ($request->confirm_cash_collection) {
            $payResponse = app(WalletService::class)->payCash($order);
        } else{
            if(!$order->invoice|| $order->invoice->unpaidAmount() > 0){
              return ['status' => false, 'message' => 'Invoice is not paid'];
            }
            $payResponse = app(WalletService::class)->distributeFunds($order);
        }

        if (!$payResponse['status']) {
            return ['status' => false, 'message' => $payResponse['message']];
        }

        $this->orderRepository->updateOrderToDone($order);

        // Update old statistics (keep for backward compatibility)
        $this->providerStatisticsService->handleOrderCompletion($providerId);

        // Update new monthly metrics (ProviderMetric) - incremental update
        $this->metricUpdateService->updateOrderMetrics($order->provider);

        // Evaluate and update provider level if needed
        $this->levelEvaluationService->evaluateProvider($order->provider);

        $this->pushToSocket($order);

        $this->updateReviewStatisticAfterAction($order->provider);
        $this->updateReviewStatisticAfterAction($order->user);
        $this->updateUserAction($order);
        return ['status' => true, 'message' => 'Order done successfully'];
    }
    public function pushToSocket(Order $order): void
    {
        $socketService = new SocketService();
        $data = new OrderResource($order);
        $event = 'order_done';
        $msg = "Order done with id {$order->id}";
        $user_id = $order->user_id;
        $socketService->push('user',$data,[$user_id], $event, $msg);
        $socketService->push('provider',$data,[$order->provider_id], $event, $msg);
    }

    public function updateOrderPrice($order, $newPrice)
    {
        return DB::transaction(function () use ($order, $newPrice) {
            $order->price = $newPrice;
            $walletService = app(WalletService::class);
            $invoice = $order->invoice;
            if (!$invoice) {
                $walletService->createInvoice($order);
            }
            $walletService->updateInvoiceAdditionalCost($invoice, $order);
            $order->save();
            return $order;
        });
    }

    private function notFoundException($message): void
    {
        throw new NotFoundHttpException($message);
    }

    /**
     * Update or create the ReviewStatistic and increment the completed_orders.
     *
     * @param Model $model
     * @return ReviewStatistic
     */
    public function updateReviewStatisticAfterAction(Model $model): ReviewStatistic
    {
        // Retrieve the related ReviewStatistic or create a new one if it doesn't exist
        $reviewStatistic = $model->reviewStatistics()->firstOrCreate([], [
            'total_reviews' => 0, // Default value if it's a new review statistic
            'average_rating' => 0.00, // Default value if it's a new review statistic
        ]);

        // Increment the completed_orders count by 1
        $reviewStatistic->completed_orders += 1;

        // Save the updated ReviewStatistic
        $reviewStatistic->save();

        return $reviewStatistic;
    }

    private function updateUserAction($order): void
    {
        Log::channel('test')->info('updateUserAction', ['order' => $order]);

        $user_id = $order->user_id;
        $provider_id = $order->provider_id;

        // user
        $existingReviewByUser = Review::where('order_id', $order->id)
            ->where('reviewable_type', User::class)
            ->where('reviewable_id', $user_id)
            ->exists();

        if (!$existingReviewByUser) {
            UserAction::showRateLastOrderForUser($user_id, $order->id);
        }
        // provider
        $existingReviewByProvider = Review::where('order_id', $order->id)
            ->where('reviewable_type', Provider::class)
            ->where('reviewable_id', $provider_id)
            ->exists();
        Log::channel('test')->info('existingReviewByProvider', ['value' => $existingReviewByProvider]);

        if (!$existingReviewByProvider) {
            UserAction::showRateLastOrderForProvider($provider_id, $order->id);
        }


    }

}
