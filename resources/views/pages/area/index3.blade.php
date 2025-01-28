<x-app-layout>
    <x-slot name="toolbar">
{{--        @include('partials.toolbar')--}}
    </x-slot>
    <!--begin::Content container-->
    <div id="kt_app_content_container" class="app-container container-xxl">
        <!--begin::Card-->
        <div class="card">
            <!--begin::Card header-->
            <div class="card-header border-0 pt-6">
                <!--begin::Card title-->
                <div class="card-title">
                    <!--begin::Search-->
                    <div class="d-flex align-items-center position-relative my-1">
                        <i class="ki-duotone ki-magnifier fs-3 position-absolute ms-5">
                            <span class="path1"></span>
                            <span class="path2"></span>
                        </i>
                        <input type="text" data-kt-user-table-filter="search" class="form-control form-control-solid w-250px ps-13" placeholder="Search user" id="mySearchInput"/>
                    </div>
                    <!--end::Search-->
                </div>
                <!--begin::Card title-->

                <!--begin::Card toolbar-->
                <div class="card-toolbar">
                    <!--begin::Toolbar-->
                    <div class="d-flex justify-content-end" data-kt-user-table-toolbar="base">
                        <!--begin::Add user-->
                        <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#kt_modal_add_client">
                            <i class="ki-duotone ki-plus fs-2"></i>Add User
                        </button>
                        <!--end::Add user-->
                    </div>
                    <!--end::Toolbar-->

                    <!--begin::Modal-->
{{--                    <livewire:add-client></livewire:add-client>--}}
                    <!--end::Modal-->
                </div>
                <!--end::Card toolbar-->
            </div>
            <!--end::Card header-->

            <!--begin::Card body-->
            <div class="card-body py-4">
                <!--begin::Table-->
                <div class="table-responsive">
                    {{ $dataTable->table() }}
                </div>
                <!--end::Table-->
            </div>
            <!--end::Card body-->
        </div>
        <!--end::Card-->
    </div>
    <!--end::Content container-->
    @push('scripts')
        {{ $dataTable->scripts(attributes: ['type' => 'module']) }}
        <script>
            document.getElementById('mySearchInput').addEventListener('keyup', function () {
                // console.log(document.getElementById(window.LaravelDataTables['users-table']))
                window.LaravelDataTables['users-table'].search(this.value).draw();
            });

            // Assuming there's a 'success' event triggered somewhere else in your code
            document.addEventListener('success', function () {
                document.getElementById('kt_modal_add_user').style.display = 'none';
                window.LaravelDataTables['users-table'].ajax.reload();
            });
        </script>
    @endpush
</x-app-layout>
