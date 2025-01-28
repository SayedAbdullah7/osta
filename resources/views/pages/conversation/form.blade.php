@php
    $isEdit = isset($model);
    $actionRoute = $isEdit ? route('ticket.update', [$model->id]) : route('ticket.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">
<!-- Country -->

<x-select
    label="status"
    name="status"
    :value="$isEdit ? $model->status : ''"
    :options="['open' => 'open','pending' => 'pending', 'closed' => 'closed']"
    required
/>
</x-form>
