@php
    $isEdit = isset($model); // Check if editing or creating
    $actionRoute = $isEdit
        ? route('service.update', [$model->id])  // Use update route if editing
        : route('service.store');  // Use store route if creating a new entry
    $method = $isEdit ? 'PUT' : 'POST';  // Set HTTP method (PUT for edit, POST for create)
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">

        <!-- Dynamic Language Fields for Translations -->
        @foreach(config('app.locales') as $locale => $language)
            <x-group-input-text
                label="Name ({{ $language }})"
                name="name[{{ $locale }}]"
                :value="$isEdit ? $model->translate($locale)?->name : old('name.' . $locale)"
                required
            />
        @endforeach
{{--                name="{{ $locale }}['name']"--}}
{{--        <x-group-input-text label="name" value="{{isset($model)?$model->name:''}}" name="name"></x-group-input-text>--}}

        <x-select
            label="Category"
            name="category"
            :options="\App\Enums\OrderCategoryEnum::toArray()"
            old="{{isset($model)?$model->category:''}}"
            required
        />

        <!-- Media Upload Component with Dynamic Options -->
        <x-media-upload :max-files="1" :max-filesize="1" accepted-files="image/*" :min-files="isset($model) ? 0 : 1"  />

        @if(isset($model))
            <div class="row">
                <x-media-gallery :media-items="$model->getMedia()??[]" />
            </div>
        @endif

</x-form>
