@php
    $isEdit = isset($model); // Check if editing or creating
    $actionRoute = $isEdit
        ? route('sub-service.update', [$model->id])  // Use update route if editing
        : route('sub-service.store');  // Use store route if creating a new entry
    $method = $isEdit ? 'PUT' : 'POST';  // Set HTTP method (PUT for edit, POST for create)
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">


        <!-- Name Input -->
        @foreach(config('app.locales') as $locale => $language)
            <x-group-input-text
                label="Name ({{ $language }})"
                name="name[{{ $locale }}]"
                :value="$isEdit ? $model->translate($locale)?->name : old('name.' . $locale)"
                required
            />
        @endforeach

        <!-- Max Price Input -->
        <x-group-input-text label="Max Price" type="number" value="{{ isset($model) ? $model->max_price : '' }}" name="max_price" step="0.01"></x-group-input-text>

{{--        <!-- Type Input -->--}}
{{--        <x-group-input-text label="Type" value="{{ isset($model) ? $model->type : '' }}" name="type" required></x-group-input-text>--}}

        <!-- Service Selection -->
        <x-select
            label="Service"
            name="service_id"
            :options="\App\Models\Service::pluck('id','name')->toArray()"
            old="{{isset($model)?$model->service_id:''}}"
            required
        />

        <!-- Type Selection -->
        <x-select
            label="Type"
            name="type"
            :options="[
            'New' => 'new',
            'Old' => 'old',
        ]"
            old="{{isset($model)?$model->type:''}}"
        />

        <!-- Media Upload Component with Dynamic Options -->
        <x-media-upload :max-files="1" :max-filesize="1" accepted-files="image/*" />

        <!-- Media Gallery -->
        @if(isset($model))
            <div class="row">
                <x-media-gallery :media-items="$model->getMedia()??[]" />
            </div>
        @endif

</x-form>
