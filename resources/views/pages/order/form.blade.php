@php
    $isEdit = isset($model);
    $actionRoute = $isEdit ? route('order.update', [$model->id]) : route('order.store');
    $method = $isEdit ? 'PUT' : 'POST';
    if ($model->status == \App\Enums\OrderStatusEnum::ACCEPTED) {
        $options = [
        'Done' => \App\Enums\OrderStatusEnum::DONE->value,
        'Cancelled' => \App\Enums\OrderStatusEnum::CANCELED->value,
            ];
    }elseif($model->status == \App\Enums\OrderStatusEnum::PENDING){
        $options = [
        'Cancelled' => \App\Enums\OrderStatusEnum::CANCELED->value,
            ];
    }else{
        $options =[];
    }
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">
    <!-- Country -->

    <x-select
        label="status"
        name="status"
        :value="$isEdit ? $model->status->value : ''"
        :options="$options"
        required
    />
</x-form>
