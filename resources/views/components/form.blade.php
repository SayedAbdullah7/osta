@props([
    'actionRoute' => '',
    'method' => 'POST',
    'isEdit' => false,
])

<form id="kt_modal_form" class="form" action="{{ $actionRoute }}" method="post" data-method="{{ $method }}">
    @csrf
    @if($isEdit)
        @method('PUT')
    @endif
    <!--begin::Scroll-->
    <div class="d-flex flex-column scroll-y px-5 px-lg-10" id="kt_modal_scroll" data-kt-scroll="true"
         data-kt-scroll-activate="true" data-kt-scroll-max-height="auto" data-kt-scroll-dependencies="#kt_modal_header"
         data-kt-scroll-wrappers="#kt_modal_scroll" data-kt-scroll-offset="300px">

    {{ $slot }}

    </div>
    <!--end::Scroll-->

    <!-- Actions -->
    <div class="text-center pt-10">
        <button type="reset" class="btn btn-light me-3 close" data-kt-users-modal-action="cancel" data-bs-dismiss="modal">
            Discard
        </button>
        <button type="submit" class="btn btn-primary" data-kt-users-modal-action="submit">
            <span class="indicator-label">Submit</span>
            <span class="indicator-progress">Please wait...
            <span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
        </button>
    </div>
</form>
