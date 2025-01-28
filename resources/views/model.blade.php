

<div class="modal fade" id="modal-form"  tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-650px">
        <!--begin::Modal content-->
        <div class="modal-content">
            <!--begin::Modal header-->
            <div class="modal-header" id="kt_modal_add_user_header">
                <!--begin::Modal title-->
{{--                <h2 class="fw-bold">Add User</h2>--}}
                <!--end::Modal title-->
                <!--begin::Close-->
                <div class="btn btn-icon btn-sm btn-active-icon-primary close" data-kt-users-modal-action="close" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1">
                        <span class="path1"></span>
                        <span class="path2"></span>
                    </i>
                </div>
                <!--end::Close-->
            </div>
            <!--end::Modal header-->
            <!--begin::Modal body-->
            <div class="modal-body px-5 my-7">
                <div class="d-block  overflow-auto" style="max-height: 550px">
                    <div id="loader" class="load" style="display: none; height: 370px;">
                        <img src="{{ asset('images/gif/loading.gif') }}" height="370" width="100%">
                    </div>
                    <div class="append" id="content">
                    </div>
                    {{--                <div class="card rounded-0 bg-light-dark ">--}}
                    {{--                </div>--}}

                </div>

            </div>
            <!--end::Modal body-->
        </div>
        <!--end::Modal content-->
    </div>
</div>
