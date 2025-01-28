    <x-table>
        <x-slot name="toolbar">
            <!--begin::Filter-->
            <button type="button" class="btn btn-light-primary me-3" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end">
                <i class="ki-duotone ki-filter fs-2">
                    <span class="path1"></span>
                    <span class="path2"></span>
                </i>Filter</button>
            <!--begin::Menu 1-->
            <div class="menu menu-sub menu-sub-dropdown w-300px w-md-325px" data-kt-menu="true">
                <!--begin::Header-->
                <div class="px-7 py-5">
                    <div class="fs-5 text-gray-900 fw-bold">Filter Options</div>
                </div>
                <!--end::Header-->
                <!--begin::Separator-->
                <div class="separator border-gray-200"></div>
                <!--end::Separator-->
                <!--begin::Content-->
                <div class="px-7 py-5" data-kt-user-table-filter="form">
                    @foreach ($filters as $column => $filter)
                        <div class="col">
                            @if ($filter['type'] === 'select')
                                <!--begin::Input group-->
                                <div class="mb-10">
                                    <label class="form-label fs-6 fw-semibold">{{$filter['label']}}</label>
                                    <select id="filter_{{ $column }}" class="form-select form-select-solid fw-bold table-filter" data-kt-select2="true" data-placeholder="Select option" data-allow-clear="true" data-kt-user-table-filter="{{$column}}" data-hide-search="true">
                                        <option></option>
                                        @foreach ($filter['options'] as $key => $value)
                                            <option value="{{ $key }}">{{ $value }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <!--end::Input group-->
                            @elseif ($filter['type'] === 'date')
                                <!--begin::Input group-->
                                <div class="mb-10">
                                    <x-group-input-date id="filter_{{ $column }}" class="table-filter" name="filter_{{ $column }}" :label="$filter['label']" :min="$filter['min']" :max="$filter['max']"></x-group-input-date>
                                </div>
                            @endif
                        </div>
                    @endforeach
                    <!--begin::Actions-->
                    <div class="d-flex justify-content-end">
                        <button type="reset" class="btn btn-light btn-active-light-primary fw-semibold me-2 px-6" data-kt-menu-dismiss="true" data-kt-user-table-filter="reset">Reset</button>
                        <button type="submit" class="btn btn-primary fw-semibold px-6" data-kt-menu-dismiss="true" data-kt-user-table-filter="filter">Apply</button>
                    </div>
                    <!--end::Actions-->
                </div>
                <!--end::Content-->
            </div>
            <!--end::Menu 1-->
            <!--end::Filter-->
{{--            @foreach ($filters as $column => $filter)--}}
{{--                <div class="col">--}}
{{--                    @if ($filter['type'] === 'text')--}}
{{--                        <input--}}
{{--                            type="text"--}}
{{--                            id="filter_{{ $column }}"--}}
{{--                            class="form-control"--}}
{{--                            placeholder="{{ $filter['placeholder'] ?? '' }}"--}}
{{--                        >--}}
{{--                    @elseif ($filter['type'] === 'select')--}}
{{--                        <select--}}
{{--                            id="filter_{{ $column }}"--}}
{{--                            class="form-control"--}}
{{--                        >--}}
{{--                            <option value="">All</option>--}}
{{--                            @foreach ($filter['options'] as $key => $value)--}}
{{--                                <option value="{{ $key }}">{{ $value }}</option>--}}
{{--                            @endforeach--}}
{{--                        </select>--}}
{{--                    @elseif ($filter['type'] === 'date')--}}
{{--                        <input--}}
{{--                            type="date"--}}
{{--                            id="filter_{{ $column }}"--}}
{{--                            class="form-control"--}}
{{--                        >--}}
{{--                    @endif--}}
{{--                </div>--}}
{{--            @endforeach--}}
        </x-slot>

        <!--begin::Datatable-->
        <table id="{{ $tableId }}" class="table align-middle table-row-dashed fs-6 gy-5">
            <thead>
            <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                @if($showCheckbox)
                    <th class="w-10px pe-2">
                        <div class="form-check form-check-sm form-check-custom form-check-solid me-3">
                            <input class="form-check-input" type="checkbox" data-kt-check="true"
                                   data-kt-check-target="#{{ $tableId }} .form-check-input" value="1"/>
                        </div>
                    </th>
                @endif
                            @foreach($columns as $column)
                                <th>{{ $column->title }}</th>
                            @endforeach
{{--                <th>id</th>--}}
{{--                <th>Name</th>--}}
{{--                <th>Phone</th>--}}
{{--                <th>Email</th>--}}
{{--                <th>Phone Verified</th>--}}
{{--                <th>Gender</th>--}}
{{--                <th>Date of Birth</th>--}}
{{--                                <th>Country</th>--}}
                @if($actions)
                    <th class="text-end min-w-100px">Actions</th>
                @endif
            </tr>
            </thead>
            <tbody class="text-gray-600 fw-semibold"></tbody>
        </table>
        <!--end::Datatable-->
    </x-table>
@push('scripts')
    <script>
        "use strict";

        // Class definition
        var KTDatatablesServerSide = function () {
            {{--const columns =  @json($columns).map(item => ({--}}
            {{--    data: item.name,--}}
            {{--    name: item.name--}}
            {{--}));--}}
            const columns =  @json($JsColumns);
            const filters = @json($filters);

            console.log('columns')
            console.log(columns)
            console.log([
                    @if($showCheckbox)
                {data: '', name: ''},
                    @endif
                {data: 'id', name: 'id'},
                {data: 'name', name: 'name'},
                // {data: 'phone', name: 'phone'},
                // {data: 'email', name: 'email'},
                // {data: 'is_phone_verified', name: 'is_phone_verified'},
                // {data: 'gender', name: 'gender'},
                // {data: 'date_of_birth', name: 'date_of_birth'},
                // {data: 'country', name: 'country'},
                    @if($actions)
                {data: 'action', name: 'action'},
                @endif

            ],)

            const tableId = '{{ $tableId }}';
            // Shared variables
            var table;
            var dt;
            var filterPayment;

            // Private functions
            var initDatatable = function () {
                dt = $('#' + tableId).DataTable({
                    searchDelay: 500,
                    processing: true,
                    serverSide: true,
                    // order: [[5, 'desc']],
                    {{--order: [[{{ $defaultOrder['column'] }}, '{{ $defaultOrder['direction'] }}']],--}}
                    stateSave: true,
                    select: {
                        style: 'multi',
                        selector: 'td:first-child input[type="checkbox"]',
                        className: 'row-selected'
                    },
                    // ajax: {
                    //     url: "https://preview.keenthemes.com/api/datatables.php",
                    // },
                    {{--                    ajax: '{{ route('datatable.data') }}',--}}
{{--                    ajax: '{{ $ajaxUrl }}',--}}
                    ajax: {
                        url: '{{ $ajaxUrl }}',
                        data: function (d) {
                            // Dynamically append filter data
                            // Object.keys(filters).forEach(function (filterKey) {
                            //     console.log(filterKey)
                            //     const filterValue = $(`#filter_${filterKey}`).val();
                            //     if (filterValue) {
                            //         d[filterKey] = filterValue;
                            //     }
                            // });
                            console.log(d)
                        },
                    },
                    columns: columns,
                    columnDefs: [
                            @if($showCheckbox)
                        {
                            targets: 0,
                            orderable: false,
                            render: function (data) {
                                return `
                            <div class="form-check form-check-sm form-check-custom form-check-solid">
                                <input class="form-check-input" type="checkbox" value="${data}" />
                            </div>`;
                            }
                        },
                        @endif
                        // {
                        //     targets: 4,
                        //     render: function (data, type, row) {
                        //         return `<img src="${hostUrl}media/svg/card-logos/${row.CreditCardType}.svg" class="w-35px me-3" alt="${row.CreditCardType}">` + data;
                        //     }
                        // },
                        @if($actions)
                        {
                            targets: -1,
                            data: null,
                            orderable: false,
                            className: 'text-end',
                            render: function (data, type, row) {
                                return `
                            <a href="#" class="btn btn-light btn-active-light-primary btn-flex btn-center btn-sm" data-kt-menu-trigger="click" data-kt-menu-placement="bottom-end" data-kt-menu-flip="top-end">
                                Actions
                                <span class="svg-icon fs-5 m-0">
                                    <svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" width="24px" height="24px" viewBox="0 0 24 24" version="1.1">
                                        <g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd">
                                            <polygon points="0 0 24 0 24 24 0 24"></polygon>
                                            <path d="M6.70710678,15.7071068 C6.31658249,16.0976311 5.68341751,16.0976311 5.29289322,15.7071068 C4.90236893,15.3165825 4.90236893,14.6834175 5.29289322,14.2928932 L11.2928932,8.29289322 C11.6714722,7.91431428 12.2810586,7.90106866 12.6757246,8.26284586 L18.6757246,13.7628459 C19.0828436,14.1360383 19.1103465,14.7686056 18.7371541,15.1757246 C18.3639617,15.5828436 17.7313944,15.6103465 17.3242754,15.2371541 L12.0300757,10.3841378 L6.70710678,15.7071068 Z" fill="currentColor" fill-rule="nonzero" transform="translate(12.000003, 11.999999) rotate(-180.000000) translate(-12.000003, -11.999999)"></path>
                                        </g>
                                    </svg>
                                </span>
                            </a>
                            <!--begin::Menu-->
                            <div class="menu menu-sub menu-sub-dropdown menu-column menu-rounded menu-gray-600 menu-state-bg-light-primary fw-bold fs-7 w-125px py-4" data-kt-menu="true">
                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3" data-kt-user-table-filter="edit_row">
                                        Edit
                                    </a>
                                </div>
                                <!--end::Menu item-->

                                <!--begin::Menu item-->
                                <div class="menu-item px-3">
                                    <a href="#" class="menu-link px-3" data-kt-user-table-filter="delete_row">
                                        Delete
                                    </a>
                                </div>
                                <!--end::Menu item-->
                            </div>
                            <!--end::Menu-->
                        `;
                            },
                        },
                        @endif
                    ],
                    // Add data-filter attribute
                    // createdRow: function (row, data, dataIndex) {
                    //     $(row).find('td:eq(4)').attr('data-filter', data.CreditCardType);
                    // }
                    @if(isset($onRowRender))
                    createdRow: {{ $onRowRender }},
                    @endif
                });

                table = dt.$;
                console.log(table)

                // Re-init functions on every table re-draw -- more info: https://datatables.net/reference/event/draw
                dt.on('draw', function () {
                    initToggleToolbar();
                    toggleToolbars();
                    handleDeleteRows();
                    KTMenu.createInstances();
                });
            }

            // Search Datatable --- official user reference: https://datatables.net/reference/api/search()
            var handleSearchDatatable = function () {
                const filterSearch = document.querySelector('[data-kt-user-table-filter="search"]');
                filterSearch.addEventListener('keyup', function (e) {
                    dt.search(e.target.value).draw();
                    console.log('seaching..')
                });
            }

            // Filter Datatable
            var handleFilterDatatable = () => {
                // Select filter options
                filterPayment = document.querySelectorAll('[data-kt-user-table-filter="payment_type"] [name="payment_type"]');
                const filterButton = document.querySelector('[data-kt-user-table-filter="filter"]');

                // Filter datatable on submit
                filterButton.addEventListener('click', function () {
                    // Get filter values
                    let paymentValue = '';

                    // Get payment value
                    filterPayment.forEach(r => {
                        if (r.checked) {
                            paymentValue = r.value;
                        }

                        // Reset payment value if "All" is selected
                        if (paymentValue === 'all') {
                            paymentValue = '';
                        }
                    });

                    // Filter datatable --- official user reference: https://datatables.net/reference/api/search()
                    dt.search(paymentValue).draw();
                    // Object.keys(filters).forEach(function (filterKey) {
                    //     let filter = document.querySelector('#filter_' + filterKey );
                    //     console.log(filter)
                    //     console.log(filterKey)
                    //     let index = columns.findIndex(item => item.data === name);
                    //     console.log('index', index)
                    //     dt.column(5).search(filter.value).draw();
                    // });
                    Object.keys(filters).forEach(function (filterKey) {
                        console.log(filterKey)
                        let filter = document.querySelector('#filter_' + filterKey );
                        console.log(filter)
                        if (!filter) return
                        let index = columns.findIndex(item => item.data === filterKey);
                        console.log('index', index)
                        dt.column(index).search(filter.value).draw();
                    });

                });
            }

            // Delete customer
            var handleDeleteRows = () => {
                // Select all delete buttons
                const deleteButtons = document.querySelectorAll('[data-kt-user-table-filter="delete_row"]');

                deleteButtons.forEach(d => {
                    // Delete button on click
                    d.addEventListener('click', function (e) {
                        e.preventDefault();

                        // Select parent row
                        const parent = e.target.closest('tr');

                        // Get customer name
                        const customerName = parent.querySelectorAll('td')[1].innerText;

                        // SweetAlert2 pop up --- official user reference: https://sweetalert2.github.io/
                        Swal.fire({
                            text: "Are you sure you want to delete " + customerName + "?",
                            icon: "warning",
                            showCancelButton: true,
                            buttonsStyling: false,
                            confirmButtonText: "Yes, delete!",
                            cancelButtonText: "No, cancel",
                            customClass: {
                                confirmButton: "btn fw-bold btn-danger",
                                cancelButton: "btn fw-bold btn-active-light-primary"
                            }
                        }).then(function (result) {
                            if (result.value) {
                                // Simulate delete request -- for demo purpose only
                                Swal.fire({
                                    text: "Deleting " + customerName,
                                    icon: "info",
                                    buttonsStyling: false,
                                    showConfirmButton: false,
                                    timer: 2000
                                }).then(function () {
                                    Swal.fire({
                                        text: "You have deleted " + customerName + "!.",
                                        icon: "success",
                                        buttonsStyling: false,
                                        confirmButtonText: "Ok, got it!",
                                        customClass: {
                                            confirmButton: "btn fw-bold btn-primary",
                                        }
                                    }).then(function () {
                                        // delete row data from server and re-draw datatable
                                        dt.draw();
                                    });
                                });
                            } else if (result.dismiss === 'cancel') {
                                Swal.fire({
                                    text: customerName + " was not deleted.",
                                    icon: "error",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                });
                            }
                        });
                    })
                });
            }

            // Reset Filter
            var handleResetForm = () => {
                // Select reset button
                const resetButton = document.querySelector('[data-kt-user-table-filter="reset"]');

                // Reset datatable
                resetButton.addEventListener('click', function () {
                    console.log('reset')
                    // Reset payment type
                    // filterPayment[0].checked = true;

                    // Reset datatable --- official user reference: https://datatables.net/reference/api/search()
                    // dt.search('').draw();
                    dt.columns().every( function () {
                        this.search('');
                    } );
                    dt.search('').draw();

                    const filters = document.querySelectorAll('.table-filter');
                    // filters.forEach(input => {
                    //     input.value = ''; // Clear the input value
                    // });
                    filters.forEach(filter => {
                        if (filter.tagName.toLowerCase() === 'input') {
                            filter.value = ''; // Clear input field values
                        } else if (filter.tagName.toLowerCase() === 'select') {
                            filter.selectedIndex = 0; // Reset select to the first option (usually empty option)
                        }
                    });
                });
            }

            // Init toggle toolbar
            var initToggleToolbar = function () {
                // Toggle selected action toolbar
                // Select all checkboxes
                const container = document.querySelector('#' + tableId);
                const checkboxes = container.querySelectorAll('[type="checkbox"]');

                // Select elements
                const deleteSelected = document.querySelector('[data-kt-user-table-select="delete_selected"]');

                // Toggle delete selected toolbar
                checkboxes.forEach(c => {
                    // Checkbox on click event
                    c.addEventListener('click', function () {
                        setTimeout(function () {
                            toggleToolbars();
                        }, 50);
                    });
                });

                // Deleted selected rows
                deleteSelected.addEventListener('click', function () {
                    // SweetAlert2 pop up --- official user reference: https://sweetalert2.github.io/
                    Swal.fire({
                        text: "Are you sure you want to delete selected customers?",
                        icon: "warning",
                        showCancelButton: true,
                        buttonsStyling: false,
                        showLoaderOnConfirm: true,
                        confirmButtonText: "Yes, delete!",
                        cancelButtonText: "No, cancel",
                        customClass: {
                            confirmButton: "btn fw-bold btn-danger",
                            cancelButton: "btn fw-bold btn-active-light-primary"
                        },
                    }).then(function (result) {
                        if (result.value) {
                            // Simulate delete request -- for demo purpose only
                            Swal.fire({
                                text: "Deleting selected customers",
                                icon: "info",
                                buttonsStyling: false,
                                showConfirmButton: false,
                                timer: 2000
                            }).then(function () {
                                Swal.fire({
                                    text: "You have deleted all selected customers!.",
                                    icon: "success",
                                    buttonsStyling: false,
                                    confirmButtonText: "Ok, got it!",
                                    customClass: {
                                        confirmButton: "btn fw-bold btn-primary",
                                    }
                                }).then(function () {
                                    // delete row data from server and re-draw datatable
                                    dt.draw();
                                });

                                // Remove header checked box
                                const headerCheckbox = container.querySelectorAll('[type="checkbox"]')[0];
                                headerCheckbox.checked = false;
                            });
                        } else if (result.dismiss === 'cancel') {
                            Swal.fire({
                                text: "Selected customers was not deleted.",
                                icon: "error",
                                buttonsStyling: false,
                                confirmButtonText: "Ok, got it!",
                                customClass: {
                                    confirmButton: "btn fw-bold btn-primary",
                                }
                            });
                        }
                    });
                });
            }

            // Toggle toolbars
            var toggleToolbars = function () {
                // Define variables
                const container = document.querySelector('#' + tableId);
                const toolbarBase = document.querySelector('[data-kt-user-table-toolbar="base"]');
                const toolbarSelected = document.querySelector('[data-kt-user-table-toolbar="selected"]');
                const selectedCount = document.querySelector('[data-kt-user-table-select="selected_count"]');

                // Select refreshed checkbox DOM elements
                const allCheckboxes = container.querySelectorAll('tbody [type="checkbox"]');

                // Detect checkboxes state & count
                let checkedState = false;
                let count = 0;

                // Count checked boxes
                allCheckboxes.forEach(c => {
                    if (c.checked) {
                        checkedState = true;
                        count++;
                    }
                });

                // Toggle toolbars
                if (checkedState) {
                    selectedCount.innerHTML = count;
                    toolbarBase.classList.add('d-none');
                    toolbarSelected.classList.remove('d-none');
                } else {
                    toolbarBase.classList.remove('d-none');
                    toolbarSelected.classList.add('d-none');
                }
            }

            // Public methods
            return {
                init: function () {
                    initDatatable();
                    handleSearchDatatable();
                    initToggleToolbar();
                    handleFilterDatatable();
                    handleDeleteRows();
                    handleResetForm();
                }
            }
        }();

        // On document ready
        KTUtil.onDOMContentLoaded(function () {
            KTDatatablesServerSide.init();
        });

    </script>
    {{--        {{ $dataTable->scripts() }}--}}
@endpush
