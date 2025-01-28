class Main {
    constructor(id) {
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });
        this.id = id;
    }

    replace(url) {
        if (this.id)
            url = url.replace("rep_id", this.id);
        return url.replace("&amp;", "&");
    }

    delete(row, call) {
        $.ajax({
            url: this.replace(config.url),
            type: "POST",
            data: {"_method": 'DELETE'},
            success: function (response) {
                console.log(response);
                toast(response);
                if (response.status)
                    row.addClass("d-none").remove();
                if (call) {
                    call();
                }
            },
            error: function (error) {
                console.log(error);
            }
        });
    }


    login_wialon($this) {
        window.addEventListener("message", function (e) {
            if (typeof e.data == "string" && e.data.indexOf("access_token=") >= 0) {
                var token = e.data.replace("access_token=", "");
                $.ajax({
                    url: $this.data('action'),
                    type: "PUT",
                    data: {token},
                    success: function (response) {
                        console.log(response);
                        toast(response);
                    },
                    error: function (error) {
                        console.log(error);
                    }
                });
            }
        });
        var url = $this.attr("href") + "login.html?access_type=-1&user=" + $this.data('user');
        url += "&redirect_uri=" + $this.attr("href") + "post_token.html";
        window.open(url, "popupWindow", "width=600,height=600,scrollbars=yes");
    }

    form(row = null, type = null) {
        $('[data-edit=block]').attr('disabled', false);
        console.log('blck false')
        return $('body').find('form[id="form"]').validate({
            rules: config.rules,
            highlight: function (element) { // hightlight error inputs
                $(element).closest('.form-group').addClass('has-error');
            },
            success: function (label) {
                label.closest('.form-group').removeClass('has-error');
                label.remove();
            },
            errorPlacement: function (error, element) {
                error.insertAfter(element.closest('.input-group'));
            },
            submitHandler: function (form) {
                $('#modal-form').find('.load').show();
                $('#modal-form').find('.append').hide();

                $.ajax({
                    url: form.action,
                    type: form.method,
                    data: $(form).serialize(),
                    success: function (response) {
                        console.log(response);
                        if (typeof (response.status) != "undefined" && response.status !== null && !response.status && typeof (response.item) == "undefined") {
                            toast(response);
                            $('#modal-form').find('.load').hide();
                            $('#modal-form').find('.append').show();
                        } else if (type === "createByModel") {
                            toast(response);
                            $('#modal-form').modal('hide');
                        } else if (response.status && typeof (response.item) == "undefined") {
                            toast(response);
                            if (response.reload)
                                window.location.reload();
                            else {
                                $('#modal-form').modal('hide');
                                $('#modal-form').find('.load').hide();
                                $('#modal-form').find('.append').show();
                                if ($('.select2-selection').length)
                                    $('.select2-container,.select2-selection').show().children().show();
                            }

                        } else {
                            $('#modal-form').modal('hide');
                            $('#modal-form').find('.load').hide();
                            $('#modal-form').find('.append').show();
                            if ($('.select2-selection').length)
                                $('.select2-container,.select2-selection').show().children().show();
                            console.log($(form));
                            if (row) {
                                if (response.item)
                                    row.find('td').each((i, v) => {
                                        $table.cell(v).data(response.item[i]).draw().node();
                                    });
                            } else {
                                row = $table.row.add(response.item).draw().node();
                                $(row).addClass('bg-success');
                            }
                            $(row).addClass('bg-success');
                            toast(response);

                        }
                        if (typeof calendar != "undefined") {
                            var json = JSON.stringify(response.item);
                            $("#myresponse").val(json);
                            console.log(json);
                            $("#myresponse").trigger('change');
                        }

                    },
                    error: function (errors) {
                        $('.form-group').find('span').hide();
                        console.log(errors);
                        if (errors.responseJSON.errors) {
                            $.each(errors.responseJSON.errors, function (index, value) {
                                $('[name="' + index + '"]').parents('.form-group').find('span').show().html(value);
                            });

                        }
                        $('#modal-form').find('.load').hide();
                        $('#modal-form').find('.append').show();
                        $('#modal-form').find('#unit').show().children().show();
                        if ($('.select2-selection').length)
                            $('.select2-container,.select2-selection').show().children().show();
                    }
                });
            }
        });

    }

    get_com(call, row, url = null, type) {
        $('#modal-form').modal('show');
        $('#modal-form').find('.load').show();
        $('#modal-form').find('.append').hide();
        if (url)
            config.url = url;
        console.log(url)
        var successFunction = function (response) {
            console.log(response);
            $('#modal-form').find('.append').html(response);
            call(row, type);
            $('#modal-form').find('.load').hide();
            $('#modal-form').find('.append').show();
        };
        $.ajax({
            url: this.replace(config.url),
            type: "GET",
            success: successFunction,
            error: function (error) {
                console.log(error);
            }
        });
    }

    request(data = [], callback = null) {
        $.ajax({
            url: data.url,
            type: data.type,
            data: data.data ?? {},
            success: function (response) {
                console.log(response);
                toast(response);
                if (response.remove) {
                    console.log('response.remove');
                    data.row.addClass("d-none").remove();
                }
                // console.log(data);
                if (callback) {
                    if (data.inputs)
                        callback(response, data.inputs);
                    else if (data.callBackResponse)
                        callback(response);
                    else
                        callback();
                }
                if (data.row && response.item) {
                    console.log(response.item);
                    data.row.find('td').each((i, v) => {
                        $table.cell(v).data(response.item[i]).draw().node();
                    });
                    $(data.row).addClass('bg-success');
                }
            },
            error: function (error) {
                console.log(error);
            }
        });
    }
}

var toast = function (response) {
    if ($('table').hasClass("waiting-for-response")) {
        removeWaiting();
    }
    if (response.status && response.msg) {
        toastr.success(response.msg);
    } else if (response.msg) {
        toastr.error(response.msg);
    }
};

const $table = $("#data").DataTable({
    "language": {
        "lengthMenu": "Show _MENU_",
    },
    "dom":
        "<'row'" +
        "<'col-sm-6 d-flex align-items-center justify-conten-start'l>" +
        "<'col-sm-6 d-flex align-items-center justify-content-end'f>" +
        ">" +

        "<'table-responsive'tr>" +

        "<'row'" +
        "<'col-sm-12 col-md-5 d-flex align-items-center justify-content-center justify-content-md-start'i>" +
        "<'col-sm-12 col-md-7 d-flex align-items-center justify-content-center justify-content-md-end'p>" +
        ">",
    "order": [[0, "desc"]],
    "lengthMenu": [10, 25, 50, 100, 1000],
    "columnDefs": [
        {"width": "10px", "targets": 0}
    ]
});


$('body').on("click", ".has_action", function () {
    // if ($(this).data('action') == config.url){
    //     $('#modal-form').modal('show');
    // } else {
    if ($(this).closest('table').hasClass("waiting-for-response")) {
        toastr.warning('Please wait... <br> it is still being processed');
        console.log('please wait');
        return '';
    }
    main = new Main();
    type = $(this).data('type');
    config.url = $(this).data('action');
    if (type == "create")
        main.get_com(main.form, null);
    if (type == "show")
        main.get_com(main.form, null, null, type);
    else if (type == "edit")
        main.get_com(main.form, $(this).parents('tr') ?? '');
    else if (type == "createByModel")
        main.get_com(main.form, null, null, type);
    else if (type == "delete") {
        var thisElement = $(this);
        Swal.fire({
            text: "Are you sure you want to delete  ?",
            icon: "warning",
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: "Yes, delete it!",
            cancelButtonText: "No, return",
            customClass: {
                confirmButton: "btn btn-primary",
                cancelButton: "btn btn-active-light"
            }
        }).then(function (result) {
            if (result.value) {
                addWaiting(thisElement);
                main.delete(thisElement.parents("tr"))
            }
        });


    } else if (type == "restore") {
        var tr = $(this).parents("tr");
        Swal.fire({
            text: "Are you sure you want to restore  ?",
            icon: "warning",
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: "Yes, restore it!",
            cancelButtonText: "No, return",
            customClass: {
                confirmButton: "btn btn-primary",
                cancelButton: "btn btn-active-light"
            }
        }).then(function (result) {
            if (result.value) {
                main.delete(tr);
            }
        });
        // if (confirm('Are you sure you want to delete the item?')) {
        //     main.delete($(this).parents("tr"))
        // }
    }
});
swalArchiveTransaction = {
    input: 'text',
    inputLabel: 'voucher number',
    inputPlaceholder: 'enter the voucher number ...',
    inputAttributes: {
        'required': 'true'
    },
    showCancelButton: true
};
swalReview = {
    text: "Are you sure you make it reviewed ?",
    icon: "warning",
    showCancelButton: true,
    buttonsStyling: false,
    confirmButtonText: "Yes, i reviewed it!",
    cancelButtonText: "No, return",
    customClass: {
        confirmButton: "btn btn-primary",
        cancelButton: "btn btn-active-light"
    }
};

function dynamicSwal(name) {
    return {
        text: "Are you sure you make it " + name + "ed ?",
        icon: "warning",
        showCancelButton: true,
        buttonsStyling: false,
        confirmButtonText: "Yes, i'm sure",
        cancelButtonText: "No, return",
        customClass: {
            confirmButton: "btn btn-primary",
            cancelButton: "btn btn-active-light"
        }
    }
}

$('body').on("click", ".send_to_wasl,.send_to_wialon,.has_request",function () {

    if ($(this).closest('table').hasClass("waiting-for-response")) {
        toastr.warning('Please wait... <br> it is still being processed');
        console.log('please wait');
        return '';
    }
    var data;

    addWaiting($(this));
    main = new Main();
    type = $(this).data('type');
    url = $(this).data('action');
    if ($(this).data('alert')) {
        Swal.fire({
            text: "Are you sure you to continue  ?",
            icon: "warning",
            showCancelButton: true,
            buttonsStyling: false,
            confirmButtonText: "Yes",
            cancelButtonText: "No",
            customClass: {
                confirmButton: "btn btn-primary",
                cancelButton: "btn btn-active-light"
            }
        }).then(function (result) {
            if (result.value) {
                main.request({row: $(this).parents('tr'), url: url, type: type, data: {data}});
            } else
                removeWaiting($(this));
        });
    } else {
        main.request({row: $(this).parents('tr'), url: url, type: type, data: {data}});
    }
});

const search = function ($this) {
    var list = '';
    if ($('#search_list').length) {
        $('#search_list').find('.item').each((i, v) => {
            if (v.value.length) {
                if ($(v).data('model')) {
                    list += '&models[' + i + '][name]=' + $(v).data('model') + '';
                    list += '&models[' + i + '][option]=' + v.name + '';
                    list += '&models[' + i + '][value]=' + v.value + '';
                } else if ($(v).data('filter')) {
                    console.log(v.value);
                    list += '&filters[' + i + '][name]=' + $(v).data('filter') + '';
                    list += '&filters[' + i + '][option]=' + v.name + '';
                    list += '&filters[' + i + '][value]=' + v.value + '';
                } else
                    list += '&options[' + v.name + ']=' + v.value + '';
            }
        });
        fun = $('body').find('#fun');
        if (fun.length)
            list += '&_fun=' + fun.val();
        if (list.length)
            window.location.href = config.search($($this).data('model'), list);
    }
}

function removeWaiting() {
    console.log('removeWaiting');
    $('table').removeClass('waiting-for-response');
    $('.op-loading').removeClass('fa-spin').removeClass('op-loading');
}

function addWaiting(thisElement) {
    console.log('addWaiting');
    thisElement.find('i').addClass('fa-spin').addClass('op-loading');
    thisElement.closest('table').addClass('waiting-for-response');
}

function mtable(model, $this = null) {
    // localStorage.removeItem(model);
    items = localStorage.getItem(model);
    if (items && items.length) {
        items = JSON.parse(items);
    } else
        items = [];
    if ($this) {
        if ($.inArray($this.id, items) !== -1) {
            items.splice($.inArray($this.id, items), 1)
            var column = $table.column($this.id);
            column.visible(true);
            $('.col-hide[id="' + $this.id + '"]').removeClass('disabled')
        } else
            items.push($this.id);
    }
    console.log(items)
    localStorage.setItem(model, JSON.stringify(items));
    if (items.length) {
        items = jQuery.grep(items, function (n, i) {
            return (n !== "" && n != null);
        });
        items.forEach((v, i) => {
            var column = $table.column(v);
            column.visible(false);
            $('.col-hide[id="' + v + '"]').addClass('disabled')
        });
        $('#data').width("100%");
    }
}

$('.col-hide').on('click', function () {
    model = $(this).parent().data('model');
    mtable(model, this);
});

mtable($('.col-hide').parent().data('model'), null);
