<div class="fv-row mb-7">
    <!--begin::Label-->
    <label class="required fw-semibold fs-6 mb-2 text-capitalize">{{ $label }}</label>
    <!--end::Label-->
    <!--begin::Input-->
    <input {{ $attributes->except('label')->merge([
        'type' => 'text',
        'class' => 'form-control form-control-solid mb-3 mb-lg-0',
        'id' => $name,
        'name' => $name,
        'value' => $value
    ]) }}
    style="{{ $attributes->has('readonly') ? 'background-color: #f0f0f0; color: #888; cursor: not-allowed;' : '' }}"
    >
    <!--end::Input-->
</div>
