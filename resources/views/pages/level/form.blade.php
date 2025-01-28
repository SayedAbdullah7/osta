@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('level.update', [$model->id])
        : route('level.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">
    <!-- Level Name -->
    <x-group-input-text
        label="Name"
        name="name"
        :value="$isEdit ? $model->name : ''"
        required
    />

    <!-- Level -->
    <x-group-input-text
        label="Level"
        name="level"
        :value="$isEdit ? $model->level : ''"
    />

    <!-- Orders Required -->
    <x-group-input-text
        label="Orders Required"
        name="orders_required"
        :value="$isEdit ? $model->orders_required : ''"
        required
    />

{{--    <!-- Is Paid Checkbox -->--}}
{{--    <x-group-input-checkbox--}}
{{--        label="Is Paid"--}}
{{--        name="is_paid"--}}
{{--        :value="$isEdit ? $model->is_paid : 0"--}}
{{--    />--}}

    <!-- Percentage -->
    <x-group-input-text
        label="Percentage"
        name="percentage"
        :value="$isEdit ? $model->percentage : ''"
    />

{{--    <!-- Next Level -->--}}
{{--        <x-select--}}
{{--            label="Next Level"--}}
{{--            name="next_level_id"--}}
{{--            :options="['' => '0'] + \App\Models\Level::pluck('id', 'name')->toArray()"--}}
{{--            :old="$isEdit ? $model->next_level_id : 0"--}}

{{--        />--}}

</x-form>
