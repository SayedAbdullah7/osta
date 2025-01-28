@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('warranty.update', [$model->id])
        : route('warranty.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">
    <!-- Name -->
    @foreach(config('app.locales') as $locale => $language)
        <x-group-input-text
            label="Name ({{ $language }})"
            name="name[{{ $locale }}]"
            :value="$isEdit ? $model->translate($locale)?->name : old('name.' . $locale)"
            required
        />
    @endforeach


    <!-- Description -->
    @foreach(config('app.locales') as $locale => $language)
        <x-group-input-text
            label="description ({{ $language }})"
            name="description[{{ $locale }}]"
            :value="$isEdit ? $model->translate($locale)?->description : old('description.' . $locale)"
            required
        />
    @endforeach

{{--    <x-group-input-text--}}
{{--        label="description"--}}
{{--        name="description"--}}
{{--        :value="$isEdit ? $model->description : ''"--}}
{{--        required--}}
{{--    />--}}

    <!-- duration months -->
    <x-group-input-text
        label="duration months"
        name="duration_months"
        :value="$isEdit ? $model->duration_months : ''"
        required
    />

    <!-- percentage cost -->
    <x-group-input-text
        label="percentage cost"
        name="percentage_cost"
        :value="$isEdit ? $model->percentage_cost : ''"
    />


    <!-- Is Approved Checkbox -->
{{--    <x-group-input-checkbox--}}
{{--        label="Is Approved"--}}
{{--        name="is_approved"--}}
{{--        :value="$isEdit ? $model->is_approved : false"--}}
{{--    />--}}

</x-form>
