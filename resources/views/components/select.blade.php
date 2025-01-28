@php
if (isset($required)) {
    $required = 'required';
}else{
    $required = '';
}
if(isset($value) && !isset($old)){
    $old = $value;
}
@endphp
<!--begin::Input group-->

<div class="fv-row col-12 mb-7">
    <!--begin::Label-->
    <label class=" {{ $required }} fw-semibold fs-6 mb-2">{{ $label }}</label>
    <!--end::Label-->
    <!--begin::Input-->
    <select {{ $attributes->except(['options'])->merge(['class' => 'text-capitalize form-select form-select-solid mb-3 mb-lg-0']) }}  aria-label="{{ $label }}" {{ $required }}>
        <option value="" disabled {{ isset($old) && !$old ? 'selected' : '' }}>Select {{ strtolower($label) }}</option>
        @foreach($options as $option => $key)
            <option value="{{ $key }}" {{ (isset($old) ? $old : (old($name))) == $key ? 'selected' : '' }}>{{ $option }}</option>
        @endforeach
    </select>
    <!--end::Input-->
</div>
<!--end::Input group-->
