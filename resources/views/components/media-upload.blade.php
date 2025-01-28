<!-- Dropzone for Uploading Media -->
<div class="card card-flush py-4">
    <div class="card-header">
        <div class="card-title">
            <h2>Media Upload</h2>
        </div>
    </div>
    <div class="card-body pt-0">
        <div class="dropzone" id="dropzone_media">
            <div class="dz-message needsclick">
                <i class="ki-duotone ki-file-up text-primary fs-3x">
                    <span class="path1"></span><span class="path2"></span>
                </i>
                <div class="ms-4">
                    <h3 class="fs-5 fw-bold text-gray-900 mb-1">Drop files here or click to upload.</h3>
                    <span class="fs-7 fw-semibold text-gray-500">Upload up to {{ $maxFiles ?? 1 }} files, each up to {{ $maxFilesize ?? 1 }} MB</span>
                </div>
            </div>
        </div>
    </div>
</div>

<div id="image_inputs"></div>

{{-- Dropzone Initialization --}}
{{--<script src="https://cdn.jsdelivr.net/npm/dropzone@5.9.3/dist/min/dropzone.min.js"></script>--}}
<script>
        // Dropzone.autoDiscover = false;
        var myDropzone = new Dropzone("#dropzone_media", {
            url: "{{ route('upload-image') }}",
            paramName: "image",
            maxFiles: {{ $maxFiles ?? 1 }}, // Maximum number of files
            maxFilesize: {{ $maxFilesize ?? 1 }}, // Maximum file size in MB
            acceptedFiles: "{{ $acceptedFiles ?? 'image/*' }}",
            addRemoveLinks: true,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            init: function () {
                this.on("success", function (file, response) {
                    // Create a hidden input for each uploaded image
                    var input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'uploaded_images[]';
                    input.value = response.filename;
                    document.getElementById('image_inputs').appendChild(input);
                });
                this.on("removedfile", function (file) {
                    console.log(file);
                    // Remove the hidden input associated with the removed file
                    var input = document.querySelector(`input[data-filename="${file.name}"]`);
                    if (input) {
                        input.parentNode.removeChild(input);
                    }
                });
                this.on("error", function (file, response) {
                    Swal.fire({
                        text: "An error occurred during upload. Please try again.",
                        icon: 'error',
                        confirmButtonText: "Ok",
                        customClass: { confirmButton: "btn btn-primary" }
                    });
                });
            }
        });
        // Form submission validation
        document.getElementById('kt_modal_form').addEventListener('submit', function (e) {
            var dropzoneElement = document.getElementById('dropzone_media');
            var minFiles = {{$minFiles ?? 0}};
            var filesLength = myDropzone.files.length;

            console.log('minFiles:', minFiles);
            console.log('filesLength:', filesLength);

            if (filesLength < minFiles) {
                console.log('Condition true: Not enough files');
                e.preventDefault();  // Prevent the form from submitting
                e.stopPropagation();

                Swal.fire({
                    text: "You must upload at least " + minFiles + " files.",
                    icon: 'warning',
                    confirmButtonText: "Ok",
                    customClass: { confirmButton: "btn btn-primary" }
                }).then(() => {
                    // Optional: focus on the dropzone for better UX
                    dropzoneElement.focus();
                });

                console.log('SweetAlert displayed');
            }
        });

</script>
