@php
    $isEdit = isset($model);
    $actionRoute = $isEdit ? route('user.update', [$model->id]) : route('user.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">
    <!-- Form Fields -->

    <!-- Name Field -->
    <x-group-input-text
        label="Name"
        name="name"
        :value="$isEdit ? $model->name : old('name')"
        required
    />

    <!-- Phone Field -->
    <x-group-input-text
        label="Phone"
        name="phone"
        :value="$isEdit ? $model->phone : old('phone')"
        required
    />

    <!-- Is Phone Verified Checkbox -->
    <x-group-input-checkbox
        label="Is Phone Verified"
        name="is_phone_verified"
        :value="$isEdit ? $model->is_phone_verified : old('is_phone_verified', 0)"
    />

    <!-- Email Field -->
    <x-group-input-text
        label="Email"
        name="email"
        :value="$isEdit ? $model->email : old('email')"
    />

    <!-- Gender Field -->
    <x-select
        label="Gender"
        name="gender"
        :options="['Male' => 1, 'Female' => 0]"
        :value="$isEdit ? $model->gender : old('gender')"
        required
    />

    <!-- Date of Birth Field -->
    <x-group-input-date
        label="Date of Birth"
        name="date_of_birth"
        :value="$isEdit ? $model->date_of_birth : old('date_of_birth')"
    />

    <!-- Country Selection (Foreign Key) -->
    <x-select
        label="Country"
        name="country_id"
        :options="(new \App\Models\Country)->pluck('id', 'name')->toArray()"
        :value="$isEdit ? $model->country_id : old('country_id')"
        required
    />

{{--    <!-- Code Field -->--}}
{{--    <x-group-input-text--}}
{{--        label="Code"--}}
{{--        name="code"--}}
{{--        :value="$isEdit ? $model->code : old('code')"--}}
{{--    />--}}

{{--    <!-- Value Field -->--}}
{{--    <x-group-input-text--}}
{{--        label="Value"--}}
{{--        name="value"--}}
{{--        :value="$isEdit ? $model->value : old('value')"--}}
{{--    />--}}

{{--    <!-- Is Active Checkbox -->--}}
{{--    <x-group-input-checkbox--}}
{{--        label="Is Active"--}}
{{--        name="is_active"--}}
{{--        :value="$isEdit ? (bool)$model->is_active : old('is_active')"--}}
{{--    />--}}

{{--    <!-- Expiry Date Field -->--}}
{{--    <x-group-input-date--}}
{{--        label="Expires At"--}}
{{--        name="expires_at"--}}
{{--        :value="$isEdit ? $model->expires_at : old('expires_at')"--}}
{{--    />--}}

{{--    <!-- Type Select Field -->--}}
{{--    <x-select--}}
{{--        label="Type"--}}
{{--        name="type"--}}
{{--        :options="['Fixed Amount' => 'fixed', 'Percentage' => 'percentage']"--}}
{{--        :value="$isEdit ? $model->type : old('type')"--}}
{{--        required--}}
{{--    />--}}
</x-form>
