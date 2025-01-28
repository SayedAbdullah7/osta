<!--begin::Input group-->
<div class="fv-row mb-7">
    <!--begin::Label-->
    <label for="{{ $id ?? $name }}" class="required fw-semibold fs-6 mb-2">{{ $label }}</label>
    <!--end::Label-->

    <!--begin::Input-->
    <input
        type="date"
        name="{{ $name }}"
        id="{{ $id ?? $name }}"
        class="form-control form-control-solid mb-3 mb-lg-0 {{ $class ?? '' }}"
        placeholder=""
        value="{{$value??''}}"
        required/>
    <!--end::Input-->
</div>
<!--end::Input group-->

