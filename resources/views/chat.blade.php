{{-- Load VenoBox CSS --}}
<link href="{{ asset('assets/plugins/VenoBox/venobox.min.css') }}" rel="stylesheet"/>

<!--begin::Chat drawer-->
<x-chat-includes.drawer/>
<!--end::Chat drawer-->

@push('scripts')
    {{-- Load VenoBox JS and initialize --}}
    <script src="{{ asset('assets/plugins/VenoBox/venobox.min.js') }}"></script>
<script src="{{ asset('js/chat.js') }}"></script>
@endpush
