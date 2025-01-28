@php
    $isEdit = isset($model);
    $actionRoute = $isEdit
        ? route('review.update', [$model->id])
        : '#';
    $method = $isEdit ? 'PUT' : 'POST';
@endphp

<x-form :actionRoute="$actionRoute" :method="$method" :isEdit="$isEdit">
    <!-- Form Fields -->

    <!-- Reviewed By -->
    <x-group-input-text
        label="Reviewer"
        name="reviewed"
        :value="$isEdit && $model->reviewable ? $model->reviewable->definition() : ''"
        readonly
    />

    <!-- Reviewed Entity -->
    <x-group-input-text
        label="Reviewed "
        name="reviewed"
        :value="$isEdit && $model->reviewed ? $model->reviewed->definition() : ''"
        readonly
    />

    <!-- Comment -->
    <x-group-input-text
        label="Comment"
        name="comment"
        :value="$isEdit ? ($model->comment ?? 'N/A') : 'N/A'"
        readonly
    />

    <!-- Rating -->
    <x-group-input-text
        label="Rating"
        name="rating"
        :value="$isEdit ? ($model->rating ?? 'N/A') : 'N/A'"
        readonly
    />

    <!-- Is Approved Checkbox (Editable) -->
    <x-group-input-checkbox
        label="Is Approved"
        name="is_approved"
        :value="$isEdit ? $model->is_approved : false"
    />
</x-form>
