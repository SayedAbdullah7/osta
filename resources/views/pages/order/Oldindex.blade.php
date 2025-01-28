<x-app-layout>
    <x-slot name="toolbar">
        {{--        @include('partials.toolbar')--}}
    </x-slot>
    <x-table>
{{--        {{ $dataTable->table() }}--}}
{{--        <livewire:order-table />--}}


        <!--begin::Wrapper-->
        <div class="d-flex flex-stack mb-5">
            <!--begin::Search-->
            <div class="d-flex align-items-center position-relative my-1">
                <i class="ki-duotone ki-magnifier fs-1 position-absolute ms-6"><span class="path1"></span><span class="path2"></span></i>
                <input type="text" data-kt-docs-table-filter="search" class="form-control form-control-solid w-250px ps-15" placeholder="Search Customers"/>
            </div>
            <!--end::Search-->

            <!--begin::Toolbar-->
            <div class="d-flex justify-content-end" data-kt-docs-table-toolbar="base">
                <!--begin::Filter-->
                <button type="button" class="btn btn-light-primary me-3" data-bs-toggle="tooltip" title="Coming Soon">
                    <i class="ki-duotone ki-filter fs-2"><span class="path1"></span><span class="path2"></span></i>
                    Filter
                </button>
                <!--end::Filter-->

                <!--begin::Add customer-->
                <button type="button" class="btn btn-primary" data-bs-toggle="tooltip" title="Coming Soon">
                    <i class="ki-duotone ki-plus fs-2"></i>
                    Add Customer
                </button>
                <!--end::Add customer-->
            </div>
            <!--end::Toolbar-->

            <!--begin::Group actions-->
            <div class="d-flex justify-content-end align-items-center d-none" data-kt-docs-table-toolbar="selected">
                <div class="fw-bold me-5">
                    <span class="me-2" data-kt-docs-table-select="selected_count"></span> Selected
                </div>

                <button type="button" class="btn btn-danger" data-bs-toggle="tooltip" title="Coming Soon">
                    Selection Action
                </button>
            </div>
            <!--end::Group actions-->
        </div>
        <!--end::Wrapper-->

        <!--begin::Datatable-->
        <table id="datatable" class="table table-bordered">
            <thead>
            <tr>
                <th>#</th>
                <th>Name</th>
                <th>Email</th>
                <th>Created At</th>
                <th>Actions</th>
            </tr>
            </thead>
        </table>
        <!--end::Datatable-->


    </x-table>
    @push('scripts')
        <script>
            $(document).ready(function() {
                $('#datatable').DataTable({
                    processing: true,
                    serverSide: true,
                    ajax: '{{ route('datatable.data') }}',
                    columns: [
                        { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false },
                        { data: 'name', name: 'name' },
                        { data: 'email', name: 'email' },
                        { data: 'created_at', name: 'created_at' },
                        { data: 'actions', name: 'actions', orderable: false, searchable: false },
                    ]
                });

                // Handle delete button click
                $('#datatable').on('click', '.delete-btn', function(e) {
                    e.preventDefault();
                    const id = $(this).data('id');

                    Swal.fire({
                        title: "Are you sure?",
                        text: "You won't be able to revert this!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonText: "Yes, delete it!",
                        cancelButtonText: "Cancel"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $.ajax({
                                url: `/users/${id}`,
                                type: 'DELETE',
                                success: function() {
                                    Swal.fire("Deleted!", "The record has been deleted.", "success");
                                    $('#datatable').DataTable().ajax.reload();
                                },
                                error: function() {
                                    Swal.fire("Error!", "There was an issue deleting the record.", "error");
                                }
                            });
                        }
                    });
                });
            });
        </script>
{{--        {{ $dataTable->scripts() }}--}}
    @endpush
</x-app-layout>
