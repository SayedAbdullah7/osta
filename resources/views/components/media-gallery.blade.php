{{-- Load VenoBox CSS --}}
<link href="{{ asset('assets/plugins/VenoBox/venobox.min.css') }}" rel="stylesheet"/>

{{-- Media Gallery --}}
<div class="row">
    @foreach($mediaItems ?? [] as $image)
        <div class="col-6">
            <div class="card h-100 m-1">
                <a class="my-image-links" data-gall="gallery01" href="{{ asset('/storage/'.$image->id.'/'.$image->file_name) }}">
                    <img class="img-thumbnail img-fluid" src="{{ asset('/storage/'.$image->id.'/'.$image->file_name) }}" alt="Media Image">
                </a>
            </div>
        </div>
    @endforeach
</div>

{{-- Load VenoBox JS and initialize --}}
<script src="{{ asset('assets/plugins/VenoBox/venobox.min.js') }}"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        new VenoBox({
            selector: '.my-image-links',
            numeration: true,
            infinigall: true,
            share: true,
            spinner: 'rotating-plane'
        });
    });
</script>
