<!--begin:: Avatar -->
<div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
    <a href="{{ route('area.show', $area) }}">
        @if($area->profile_photo_url)
            <div class="symbol-label">
                <img src="{{ $area->profile_photo_url }}" class="w-100"/>
            </div>
        @else
            <div class="symbol-label fs-3 bg-light-primary text-success">
                {{ substr($area->name, 0, 1) }}
            </div>
        @endif
    </a>
</div>
<!--end::Avatar-->
<!--begin::User details-->
<div class="d-flex flex-column">
    <a href="{{ route('area.show', $area) }}" class="text-gray-800 text-hover-primary mb-1">
        {{ $area->name }}
    </a>
    <span>{{ $area->email }}</span>
</div>
<!--begin::User details-->
