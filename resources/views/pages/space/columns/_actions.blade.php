{{--<a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">Actions--}}
{{--    <i class="ki-duotone ki-down fs-5 ms-1"></i></a>--}}
{{--<!--begin::Menu-->--}}
{{--<div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-semibold fs-7 w-125px py-4" data-kt-menu="true">--}}
{{--    <!--begin::Menu item-->--}}
{{--    <div class="menu-item px-3">--}}
{{--        <a href="../../demo1/dist/apps/user-management/users/view.html" class="menu-link px-3">Edit</a>--}}
{{--    </div>--}}
{{--    <!--end::Menu item-->--}}
{{--    <!--begin::Menu item-->--}}
{{--    <div class="menu-item px-3">--}}
{{--        <a href="#" class="menu-link px-3" data-kt-users-table-filter="delete_row">Delete</a>--}}
{{--    </div>--}}
{{--    <!--end::Menu item-->--}}
{{--</div>--}}
{{--<!--end::Menu-->--}}
{{--<a href="#" class="has_action element btn btn-icon btn-light-primary me-5 " data-type="show"--}}
{{--   data-action="{{ route('service.show',$spaceSubService->id) }}" data-method="get"><i class="fa-solid fa-eye"></i></a>--}}
<a href="#" class="has_action btn btn-icon btn-light-warning me-5 " data-type="edit"
   data-action="{{route('space_sub_service.edit', [$spaceSubService->space_id, $spaceSubService->sub_service_id])}}">
    <i class="fa-solid fa-pen-to-square"></i>
</a>
<a href="#" class="delete_btn btn btn-icon btn-light-danger me-5  delete_btn" data-type="delete" data-action="{{route('space_sub_service.edit', [$spaceSubService->space_id, $spaceSubService->sub_service_id])}}" >
    <i class="fa-solid fa-trash"></i>
</a>
{{--<a href="#" class="btn btn-icon btn-light-danger me-5 " data-bs-toggle="modal" data-bs-target="#kt_modal_add_user"><i--}}
{{--        class="fa-solid fa-trash"></i></a>--}}
{{--<a class="has_action btn btn-icon btn-light btn-hover-primary btn-sm " data-type="edit" data-action="mymodel"--}}
{{--   data-bs-custom-class="tooltip-dark" data-bs-toggle="tooltip" data-bs-placement="top" title="show">--}}
{{--    <i class="fas fa-eye text-primary cursor-pointer"></i>--}}
{{--</a>--}}
