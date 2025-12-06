@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('provider.update', [$model->id])
        : route('provider.store');
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">
{{--    <!-- First Name -->--}}
{{--    <x-group-input-text--}}
{{--        label="First Name"--}}
{{--        name="first_name"--}}
{{--        :value="$isEdit ? $model->first_name : ''"--}}
{{--        required--}}
{{--    />--}}

{{--    <!-- Last Name -->--}}
{{--    <x-group-input-text--}}
{{--        label="Last Name"--}}
{{--        name="last_name"--}}
{{--        :value="$isEdit ? $model->last_name : ''"--}}
{{--        required--}}
{{--    />--}}
        <!-- Last Name -->
        <x-group-input-text
            label="name"
            name="name"
            :value="$isEdit ? $model->name : ''"
            required
        />

    <!-- Phone -->
    <x-group-input-text
        label="Phone"
        name="phone"
        :value="$isEdit ? $model->phone : ''"
        required
    />

    <!-- Email -->
    <x-group-input-text
        label="Email"
        name="email"
        :value="$isEdit ? $model->email : ''"
    />

    <!-- Gender -->
    <x-select
        label="Gender"
        name="gender"
        :value="$isEdit ? $model->gender : ''"
        :options="['Male' => 1, 'Female' => 0]"
        required
    />


    <!-- Country -->
    <x-select
        label="Country"
        name="country_id"
        :value="$isEdit ? $model->country_id : ''"
        :options="(new \App\Models\Country)->pluck('id', 'name')->toArray()"
        required
    />

    <!-- City -->
    <x-select
        label="City"
        name="city_id"
        :value="$isEdit ? $model->city_id : ''"
        :options="(new \App\Models\City())->pluck('id', 'name')->toArray()"
        required
    />

    <!-- Is Approved Checkbox -->
    <x-group-input-checkbox
        label="Is Approved"
        name="is_approved"
        :value="$isEdit ? $model->is_approved : false"
    />

    <!-- Media Upload Component with Dynamic Options -->
{{--    <x-media-upload :max-files="1" :max-filesize="1" accepted-files="image/*" :min-files="isset($model) ? 0 : 1"  />--}}

{{--    @if(isset($model))--}}
{{--        <div class="row">--}}
{{--            <x-media-gallery :media-items="$model->getMedia()??[]" />--}}
{{--        </div>--}}
{{--    @endif--}}
</x-form>
