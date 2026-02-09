<!--begin::Form-->
<form id="kt_modal_form" class="form" action="{{isset($model)?
route('space_sub_service.update', [$model->space_id, $model->sub_service_id])
:route('space_sub_service.store')}}" method="post" data-method="{{isset($model)?'PUT':'POST'}}">
    @csrf
    @if(isset($model))
        @method('PUT')
    @endif
    <!--begin::Scroll-->
    <div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_scroll" data-kt-scroll="true"
         data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_header"
         data-kt-scroll-wrappers="#kt_modal_scroll" data-kt-scroll-offset="300px">


        <!-- Max Price Input -->
        <x-group-input-text label="Max Price" type="number" value="{{ isset($model) ? $model->max_price : '' }}" name="max_price" step="0.01"></x-group-input-text>

        <!-- Description Input -->
        <div class="fv-row mb-7">
            <label class="fw-semibold fs-6 mb-2 text-capitalize">Description</label>
            <textarea
                class="form-control form-control-solid mb-3 mb-lg-0"
                name="description"
                id="description"
                rows="4"
            >{{ isset($model) ? $model->description : old('description') }}</textarea>
        </div>
        <!-- Service Selection -->
        <x-select
            label="sub service"
            name="sub_service_id"
            :options="\App\Models\SubService::whereHas('service', function ($query) {
                $query->whereIn('category', [\App\Enums\OrderCategoryEnum::SpaceBased, \App\Enums\OrderCategoryEnum::Other]);
            })->pluck('id','name')->toArray()"
            old="{{isset($model)?$model->sub_service_id:''}}"
            required
            style="pointer-events:{{isset($model)?'none':''}};"
        />

        <!-- Space Selection -->
        <x-select
            label="space"
            name="space_id"
            :options="\App\Models\Space::pluck('id','name')->toArray()"
            old="{{isset($model)?$model->space_id:''}}"
            required
            style="pointer-events:{{isset($model)?'none':''}};"
        />
    </div>


    <!--end::Scroll-->
    <!--begin::Actions-->
    <div class="text-center pt-10">
        <button type="reset" class="btn btn-light me-3 close"
                data-kt-users-modal-action="cancel" data-bs-dismiss="modal">Discard
        </button>
        <button type="submit" class="btn btn-primary"
                data-kt-users-modal-action="submit">
            <span class="indicator-label">Submit</span>
            <span class="indicator-progress">Please wait...
            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
        </button>
    </div>
    <!--end::Actions-->
</form>
<!--end::Form-->

