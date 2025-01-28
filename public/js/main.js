document.addEventListener('DOMContentLoaded', function () {
    // Translation object
    const translations = {
        en: {
            confirmDelete: "Are you sure you want to delete this item?",
            deleteSuccess: "Item deleted successfully.",
            deleteError: "An error occurred while trying to delete the item. Please try again.",
            csrfError: "CSRF token mismatch. Please refresh the page and try again.",
            genericError: "An error occurred while attempting to delete the item. Please try again.",
            ok: "Ok, got it!",
            confirm: "Confirm", // Confirm button text
            cancel: "Cancel",
            // Add more translations as needed
        },
        ar: {
            confirmDelete: "هل أنت متأكد أنك تريد حذف هذا العنصر؟",
            deleteSuccess: "تم حذف العنصر بنجاح.",
            deleteError: "حدث خطأ أثناء محاولة حذف العنصر. يرجى المحاولة مرة أخرى.",
            csrfError: "فشل رمز CSRF. يرجى تحديث الصفحة والمحاولة مرة أخرى.",
            genericError: "حدث خطأ أثناء محاولة حذف العنصر. يرجى المحاولة مرة أخرى.",
            ok: "حسناً، فهمت!",
            confirm: "تأكيد", // Confirm button text
            cancel: "الغاء",
            // Add more translations as needed
        }
    };

    // Get current language from HTML (default to English)
    // const lang = document.documentElement.lang === "ar" ? "ar" : "en";
    const lang = 'ar';

    // Function to get translated text
    const t = (key) => translations[lang][key];

    // Function to control loader visibility
    const toggleLoader = (show) => $("#loader").toggle(show);

    // Function to control modal visibility
    const toggleModal = (show) => $("#modal-form").modal(show ? 'show' : 'hide');

    // Function to control content visibility within the modal
    const toggleContent = (show) => $('#modal-form #content').toggle(show);

    // Function to handle AJAX errors and display validation messages
    const handleAjaxError = (xhr) => {
        if (xhr.responseJSON && xhr.responseJSON.errors) {
            $('.invalid-feedback').text('').hide();
            $.each(xhr.responseJSON.errors, (key, value) => {
                const $element = $('[name="' + key + '"]');
                const $invalidFeedback = $element.siblings('.invalid-feedback');
                $invalidFeedback.text(value).show();
            });
        }
        toggleLoader(false);
        toggleContent(true);
        console.error(xhr);
        let errorMessage;

        // Check for CSRF token mismatch
        if (xhr.status === 419) {
            errorMessage = t('csrfError');
        } else if (xhr.responseJSON && xhr.responseJSON.msg) {
            errorMessage = xhr.responseJSON.msg;
        } else if (xhr.responseJSON && xhr.responseJSON.message) {
            errorMessage = xhr.responseJSON.message;
        }
        if (errorMessage) {
            showNotification(errorMessage, 'error');
        }
    };

    // Function to show a notification with SweetAlert
    const showNotification = (message, status) => {
        Swal.fire({
            text: message,
            icon: status === 'success' ? 'success' : 'error',
            buttonsStyling: false,
            confirmButtonText: t('ok'),
            customClass: {
                confirmButton: "btn btn-primary"
            }
        });
    };

    // Click handler for elements with the class 'has_action'
    $(document).on('click', '.has_action', function () {
        toggleContent(false);
        toggleLoader(true);
        toggleModal(true);

        const url = $(this).data('action');
        console.log(url);

        $.ajax({
            type: 'GET',
            url: url,
            success: (data) => {
                console.log(data);
                $('#modal-form #content').html(data);
                toggleLoader(false);
                toggleContent(true);
            },
            error: handleAjaxError
        });
    });

    // Submit handler for the form
    $(document).on('submit', '#kt_modal_form', function (event) {
        event.preventDefault();

        toggleLoader(true);
        toggleContent(false);

        const form = $(this);
        const formUrl = form.attr('action');
        const method = form.data('method');

        console.log(formUrl, method);

        $.ajax({
            url: formUrl,
            type: method,
            dataType: "json",
            data: form.serialize(),
            success: (data) => {
                console.log(data);
                showNotification(data.msg, data.status ? 'success' : 'danger');
                toggleModal(false);

                // Reload the DataTable
                    // $('#areas-table').DataTable().ajax.reload();
                    $('table').each(function() {
                        if ($.fn.DataTable.isDataTable(this)) { // Check if it's a DataTable
                            $(this).DataTable().ajax.reload();
                        }
                    });
                },
            error: handleAjaxError
        });
    });

    // Click handler for delete buttons
    $(document).on('click', '.delete_btn', function () {
        const url = $(this).data('action');

        Swal.fire({
            text: t('confirmDelete'),
            icon: "warning",
            buttonsStyling: true,
            showCancelButton: true,
            confirmButtonText: t('confirm'), // Updated line for confirmation button
            cancelButtonText: t('cancel'),
            customClass: {
                confirmButton: "btn btn-danger",
                cancelButton: "btn btn-secondary"
            }
        }).then((result) => {
            if (result.isConfirmed) {
                console.log(url);
                $.ajax({
                    type: 'DELETE',
                    url: url,
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    },
                    success: (data) => {
                        console.log(data);
                        showNotification(data.msg || t('deleteSuccess'), data.status ? 'success' : 'danger');
                        // Reload the DataTable
                        $('table').each(function() {
                            if ($.fn.DataTable.isDataTable(this)) { // Check if it's a DataTable
                                $(this).DataTable().ajax.reload();
                            }
                        });
                    },
                    error: handleAjaxError
                });
            }
        });
    });

    // Click handler to close the modal
    $(document).on('click', '.close', function () {
        $(this).closest('#modal-form').hide();
    });
});
