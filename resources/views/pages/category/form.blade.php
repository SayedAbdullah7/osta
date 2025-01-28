<!--begin::Form-->
<form id="kt_modal_form" class="form" action="{{$route}}" method="post" data-method="{{isset($model)?'PUT':'POST'}}">
    @csrf
    @if(isset($model))
        @method('PUT')
    @endif
    <!--begin::Scroll-->
    <div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_scroll" data-kt-scroll="true"
         data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_header"
         data-kt-scroll-wrappers="#kt_modal_scroll" data-kt-scroll-offset="300px">
        <x-group-input-text label="name" value="{{isset($model)?$model->name:''}}" name="name" ></x-group-input-text>
        <x-select label="section" :options="\App\Models\Category::pluck('id','name')->toArray()" old="{{isset($model)?$model->section_id:''}}" name="section_id" ></x-select>
    </div>
    <!--end::Scroll-->
    <!--begin::Actions-->
    <div class="text-center pt-10">
        <button type="reset" class="btn btn-light me-3 close" data-kt-users-modal-action="cancel">Discard</button>
        <button type="submit" class="btn btn-primary" data-kt-users-modal-action="submit">
            <span class="indicator-label">Submit</span>
            <span class="indicator-progress">Please wait...
            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
        </button>
    </div>
    <!--end::Actions-->
</form>
<!--end::Form-->

