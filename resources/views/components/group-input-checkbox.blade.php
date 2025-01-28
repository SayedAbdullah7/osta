{{--<!--begin::Input group-->--}}
{{--<div class="fv-row mb-7">--}}
{{--    <!--begin::Label-->--}}
{{--    <label class="required fw-semibold fs-6 mb-2">{{$label}}</label>--}}
{{--    <!--end::Label-->--}}
{{--    <!--begin::Input-->--}}
{{--    <input type="date" name="{{$name}}" class="form-control form-control-solid mb-3 mb-lg-0" placeholder="" value="{{$value}}" required/>--}}
{{--    <!--end::Input-->--}}
{{--</div>--}}
{{--<!--end::Input group-->--}}
<!-- resources/views/components/group-input-checkbox.blade.php -->

<div class="form-check form-switch form-check-custom form-check-solid mb-7">
    <input class="form-check-input" type="checkbox" name="{{ $name }}" value="1" id="{{$name}}checkBox" @checked($value)  />
    <label class="form-check-label" for="{{$name}}checkBox">
        {{ $label }}
    </label>
</div>
