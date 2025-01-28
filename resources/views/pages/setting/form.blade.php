@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('setting.update', [$model->id])
        : route('setting.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">
    <x-group-input-text
        label="Setting key"
        name="amount"
        :value="$isEdit ? $model->key : ''"
        required
        readonly="true"
    />

    <x-group-input-text
        label="Setting Value"
        name="value"
        :value="$isEdit ? $model->value : ''"
        required
    />
</x-form>
