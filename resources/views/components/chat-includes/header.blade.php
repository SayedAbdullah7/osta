<!--begin::Card header-->
<div class="card-header pe-5" id="kt_drawer_chat_messenger_header">
    <!--begin::Title-->
    <div class="card-title">
        <!--begin::User-->
        <div class="d-flex justify-content-center flex-column me-3">
            <a id="conversationName" href="#" class="fs-4 fw-bold text-gray-900 text-hover-primary me-1 mb-2 lh-1">Brian Cox</a>
            <!--begin::Info-->
            <div class="mb-0 lh-1">
                <span class="badge badge-success badge-circle w-10px h-10px me-1"></span>
                <span class="fs-7 fw-semibold text-muted">Active</span>
            </div>
            <!--end::Info-->
        </div>
        <!--end::User-->
    </div>
    <!--end::Title-->
    <!--begin::Card toolbar-->
    <div class="card-toolbar">
        <!--begin::Menu-->
        <div class="me-0">
            <button class="btn btn-sm btn-icon btn-active-color-primary" data-kt-menu-trigger="click"
                    data-kt-menu-placement="bottom-end">
                <i class="ki-duotone ki-dots-square fs-2">
                    <span class="path1"></span>
                    <span class="path2"></span>
                    <span class="path3"></span>
                    <span class="path4"></span>
                </i>
            </button>
            <!--begin::Menu 3-->
            <div
                class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-800 menu-state-bg-light-primary fw-semibold w-200px py-3"
                data-kt-menu="true">
                <!--begin::Heading-->
                <div class="menu-item px-3">
                    <div class="menu-content text-muted pb-2 px-3 fs-7 text-uppercase">Contacts</div>
                </div>
                <!--end::Heading-->
                <!--begin::Menu item-->
                <div class="menu-item px-3">
                    <a href="#" class="menu-link px-3" data-bs-toggle="modal"
                       data-bs-target="#kt_modal_users_search">Add Contact</a>
                </div>
                <!--end::Menu item-->
                <!--begin::Menu item-->
                <div class="menu-item px-3">
                    <a href="#" class="menu-link flex-stack px-3" data-bs-toggle="modal"
                       data-bs-target="#kt_modal_invite_friends">Invite Contacts
                        <span class="ms-2" data-bs-toggle="tooltip"
                              title="Specify a contact email to send an invitation">
										<i class="ki-duotone ki-information fs-7">
											<span class="path1"></span>
											<span class="path2"></span>
											<span class="path3"></span>
										</i>
									</span></a>
                </div>
                <!--end::Menu item-->
                <!--begin::Menu item-->
                <div class="menu-item px-3" data-kt-menu-trigger="hover" data-kt-menu-placement="right-start">
                    <a href="#" class="menu-link px-3">
                        <span class="menu-title">Groups</span>
                        <span class="menu-arrow"></span>
                    </a>
                    <!--begin::Menu sub-->
                    <div class="menu-sub menu-sub-dropdown w-175px py-4">
                        <!--begin::Menu item-->
                        <div class="menu-item px-3">
                            <a href="#" class="menu-link px-3" data-bs-toggle="tooltip" title="Coming soon">Create
                                Group</a>
                        </div>
                        <!--end::Menu item-->
                        <!--begin::Menu item-->
                        <div class="menu-item px-3">
                            <a href="#" class="menu-link px-3" data-bs-toggle="tooltip" title="Coming soon">Invite
                                Members</a>
                        </div>
                        <!--end::Menu item-->
                        <!--begin::Menu item-->
                        <div class="menu-item px-3">
                            <a href="#" class="menu-link px-3" data-bs-toggle="tooltip" title="Coming soon">Settings</a>
                        </div>
                        <!--end::Menu item-->
                    </div>
                    <!--end::Menu sub-->
                </div>
                <!--end::Menu item-->
                <!--begin::Menu item-->
                <div class="menu-item px-3 my-1">
                    <a href="#" class="menu-link px-3" data-bs-toggle="tooltip" title="Coming soon">Settings</a>
                </div>
                <!--end::Menu item-->
            </div>
            <!--end::Menu 3-->
        </div>
        <!--end::Menu-->
        <!--begin::Close-->
        <div class="btn btn-sm btn-icon btn-active-color-primary" id="kt_drawer_chat_close">
            <i class="ki-duotone ki-cross-square fs-2">
                <span class="path1"></span>
                <span class="path2"></span>
            </i>
        </div>
        <!--end::Close-->
    </div>
    <!--end::Card toolbar-->
</div>
<!--end::Card header-->
