@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('discount-code.update', [$model->id])
        : route('discount-code.store');
    $method = $isEdit ? 'PUT' : 'POST';
//$code = isset($code)?$code:'';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">
    <!-- Form Fields -->
    <x-group-input-text
        label="Code"
        name="code"
        :value="$isEdit ? $model->code : old('code',$code)"
    />

    <x-group-input-text
        label="Value"
        name="value"
        :value="$isEdit ? $model->value : old('value')"
    />

    <x-group-input-checkbox
        label="Is Active"
        name="is_active"
        :value="$isEdit ? $model->is_active : old('is_active', 1)"
    />
    <x-group-input-date
        label="Expires At"
        name="expires_at"
        :value="$isEdit ? $model->expires_at : old('expires_at')"
    />

    <x-select
        label="Type"
        name="type"
        :options="['Fixed Amount' => 'fixed', 'Percentage' => 'percentage']"
        :value="$isEdit ? $model->type : old('type')"
        required
    />

</x-form>
